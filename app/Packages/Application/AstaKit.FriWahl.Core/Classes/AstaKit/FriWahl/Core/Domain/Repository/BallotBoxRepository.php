<?php
namespace AstaKit\FriWahl\Core\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Vote;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * Repository for ballot boxes and their votes.
 *
 * The votes are part of this repository because they do not really form an aggregate root (according to the principles
 * of Domain Driven Design) – they are tied to their ballot box and wouldn't exist without it. The assignment to the
 * ballot box is however a bit random, they could also be queried via the voter or election, depending on the context
 * where they are needed – for the voter, this will automatically be done when fetching a voter object.
 *
 * @Flow\Scope("singleton")
 */
class BallotBoxRepository extends Repository {

	/**
	 * Creates a query for voters.
	 *
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 */
	public function createVotesQuery() {
		// we cannot add the constraint for the election here because $query->matching() always overwrites
		// existing constraints
		$query = $this->persistenceManager->createQueryForType('AstaKit\\FriWahl\\Core\\Domain\\Model\\Vote');
		return $query;
	}

	/**
	 * Returns the queued votes for the given ballot box
	 *
	 * @param BallotBox $ballotBox
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findQueuedVotesForBallotBox(BallotBox $ballotBox) {
		$query = $this->createVotesQuery();

		$query->matching(
			$query->logicalAnd(
				$query->equals('ballotBox', $ballotBox),
				$query->equals('status', Vote::STATUS_QUEUED)
			)
		);

		return $query->execute();
	}

}
