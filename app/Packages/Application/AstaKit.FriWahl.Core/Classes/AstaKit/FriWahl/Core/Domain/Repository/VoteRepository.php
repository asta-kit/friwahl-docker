<?php
namespace AstaKit\FriWahl\Core\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Vote;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;


/**
 * Repository for votes.
 *
 * This only exists because otherwise Flow would not recognize the corresponding entity as an aggregate root
 * and automatically remove it if objects with a relation to it are removed.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Scope("singleton")
 */
class VoteRepository extends Repository {

	/**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;


	protected function countVotesByFieldAndStatus($field, $fieldValue, $status) {
		$query = $this->createQuery();

		return $query->matching(
			$query->logicalAnd(
				$query->equals($field, $fieldValue),
				$query->equals('status', $status)
			)
		)->count();
	}

	/**
	 * Counts the queued votes for a given voting.
	 *
	 * @param Voting $voting
	 * @return int
	 */
	public function countQueuedByVoting(Voting $voting) {
		return $this->countVotesByFieldAndStatus('voting', $voting, Vote::STATUS_QUEUED);
	}

	/**
	 * Counts the committed votes for a given voting.
	 *
	 * @param Voting $voting
	 * @return int
	 */
	public function countCommittedByVoting(Voting $voting) {
		return $this->countVotesByFieldAndStatus('voting', $voting, Vote::STATUS_COMMITTED);
	}

	/**
	 * Counts the queued votes for a given voting.
	 *
	 * @param BallotBox $ballotBox
	 * @return int
	 */
	public function countQueuedByBallotBox(BallotBox $ballotBox) {
		return $this->countVotesByFieldAndStatus('ballotBox', $ballotBox, Vote::STATUS_QUEUED);
	}

	/**
	 * Counts the committed votes for a given ballot box.
	 *
	 * @param BallotBox $ballotBox
	 * @return int
	 */
	public function countCommittedByBallotBox(BallotBox $ballotBox) {
		return $this->countVotesByFieldAndStatus('ballotBox', $ballotBox, Vote::STATUS_COMMITTED);
	}

	/**
	 * Counts the voters who have cast at least one vote in the given ballot box.
	 *
	 * @param BallotBox $ballotBox
	 * @return int
	 */
	public function countVotersByBallotBox(BallotBox $ballotBox) {
		/** @var QueryBuilder $queryBuilder */
		$queryBuilder = $this->entityManager->createQueryBuilder();

		$queryBuilder
			->select('COUNT(DISTINCT vote.voter) as cnt')
			->from('AstaKit\FriWahl\Core\Domain\Model\Vote', 'vote')
			->where('vote.status = 2 AND vote.ballotBox = :ballotBox');

		$query = $queryBuilder->getQuery();
		$query->setParameter('ballotBox', $ballotBox);

		$result = $query->getResult();
		if (count($result) == 0) {
			return 0;
		} else {
			return $result[0]['cnt'];
		}
	}

	public function countByDiscriminatorValuesForVoting(Voting $voting) {
		/** @var QueryBuilder $queryBuilder */
		$queryBuilder = $this->entityManager->createQueryBuilder();

		$discriminatorIdentifier = $voting->getDiscriminator();

		$queryBuilder
			->select('disc.value AS discriminator, COUNT(vote) AS cnt')
			->from('AstaKit\FriWahl\Core\Domain\Model\Vote', 'vote')
			->leftJoin('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', 'voter', Join::WITH, 'vote.voter = voter')
			->leftJoin('AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator', 'disc', Join::WITH, 'disc.voter = voter')
			->where('vote.status = 2 AND vote.voting = :voting AND disc.identifier = :discriminator')
			->groupBy('disc.value');

		$query = $queryBuilder->getQuery();
		$query->setParameters(array(
			'voting' => $voting,
			'discriminator' => $discriminatorIdentifier,
		));

		$query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
		$result = $query->getResult();
		return $result;
	}

}
