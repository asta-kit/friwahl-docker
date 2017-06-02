<?php
namespace AstaKit\FriWahl\Core\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Domain\Model\Vote;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;


/**
 * Service for handling the participation of a voter in votings (i.e. the act of voting itself, the essence of this
 * software).
 *
 * Note that this class relies on Doctrine being used as the persistence layer – if another persistence layer is used,
 * this class has to be adjusted
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingService {

	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 *
	 * Note: Flow will inject Doctrine\ORM\EntityManager here, which exposes the database connection we need.
	 *
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;


	/**
	 * @param string $procedureName
	 * @param array $parameters
	 * @return array
	 */
	protected function callStoredProcedure($procedureName, $parameters) {
		$parameterPlaceholders = array_fill(0, count($parameters), '?');
		$query = sprintf('CALL %s (%s)',
			$procedureName, implode(', ', $parameterPlaceholders)
		);

		$result = $this->getDatabaseConnection()->executeQuery($query, $parameters);

		$row = $result->fetch();
		// if the cursor is not closed, doing other queries will fail (because the old query is still regarded
		// as "active" by the system)
		$result->closeCursor();

		// TODO define the exception hierarchy – error handling is a bit rough here still
		// TODO check for deadlock errors!
		if (isset($row['error_code'])) {
			// TODO use a dedicated SQL error exception class
			throw new \RuntimeException($row['error_msg'], $row['error_code']);
		}

		return $row;
	}


	/**
	 * Creates votes for a voter. The votes are queued, meaning they are not committed to the database finally.
	 * If the voter later on decides to not participate in the voting, the votes will be removed from the database
	 * (if they have not been committed before).
	 *
	 * @param BallotBox $ballotBox
	 * @param EligibleVoter $voter
	 * @param array $votings The votings to create votes for
	 * @return array<Vote>
	 * @throws \RuntimeException If creating a vote failed
	 */
	public function createVotes(BallotBox $ballotBox, EligibleVoter $voter, array $votings) {
		$this->getDatabaseConnection()->beginTransaction();

		$votes = array();
		foreach ($votings as $voting) {
			try {
				$votes[] = $this->createVote($ballotBox, $voter, $voting);
			// TODO define an exception hierarchy
			} catch (\Exception $e) {
				$this->getDatabaseConnection()->rollBack();

				throw new \RuntimeException('Creating votes failed: ' . $e->getMessage(), 0, $e);
			}
		}

		$this->getDatabaseConnection()->commit();

		return $votes;
	}

	/**
	 * @param BallotBox $ballotBox
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return Vote
	 * @throws \Exception
	 */
	public function createVote(BallotBox $ballotBox, EligibleVoter $voter, Voting $voting) {
		$params = array(
			$this->persistenceManager->getIdentifierByObject($voter),
			$this->persistenceManager->getIdentifierByObject($voting),
			$this->persistenceManager->getIdentifierByObject($ballotBox),
		);
		$result = $this->callStoredProcedure('register_vote', $params);

		$voteId = $result['vote_id'];
		$vote = $this->persistenceManager->getObjectByIdentifier($voteId, 'AstaKit\FriWahl\Core\Domain\Model\Vote');

		// this is only temporary for the existing object, the reference has already been stored in the vote object
		$voter->addVote($vote);

		return $vote;
	}

	/**
	 * Commits the votes pending for the given voter in the given ballot box.
	 *
	 * @param BallotBox $ballotBox
	 * @param EligibleVoter $voter
	 * @return int The number of committed votes.
	 * @throws \RuntimeException
	 */
	public function commitPendingVotesForVoter(BallotBox $ballotBox, EligibleVoter $voter) {
		$result = $this->callStoredProcedure('commit_votes_for_voter',
			array(
				$this->persistenceManager->getIdentifierByObject($voter),
				$this->persistenceManager->getIdentifierByObject($ballotBox),
			)
		);

		// TODO improve exception handling

		if (!isset($result['committed_votes'])) {
			throw new \RuntimeException('Committing votes failed for unknown reasons.', 1402995419);
		}

		return (int)$result['committed_votes'];
	}

	/**
	 * Cancels the votes pending for a user in the given ballot box.
	 *
	 * Returns the number of cancelled votes.
	 *
	 * @param BallotBox $ballotBox
	 * @param EligibleVoter $voter
	 * @return int The number of cancelled votes.
	 * @throws \RuntimeException
	 */
	public function cancelPendingVotesForVoter(BallotBox $ballotBox, EligibleVoter $voter) {
		$result = $this->callStoredProcedure('cancel_votes_for_voter',
			array(
				$this->persistenceManager->getIdentifierByObject($voter),
				$this->persistenceManager->getIdentifierByObject($ballotBox),
			)
		);

		if (!isset($result['cancelled_votes'])) {
			throw new \RuntimeException('Cancelling votes failed for unknown reasons.', 1402995419);
		}

		$cancelledVotesInVoterObject = $voter->removePendingVotes();

		return (int)$result['cancelled_votes'];
	}

	/**
	 * @return Connection
	 */
	protected function getDatabaseConnection() {
		/** @var EntityManager $this->entityManager */
		return $this->entityManager->getConnection();
	}

}
 