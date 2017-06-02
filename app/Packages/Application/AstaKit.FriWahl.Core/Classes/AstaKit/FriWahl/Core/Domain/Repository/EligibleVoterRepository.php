<?php
namespace AstaKit\FriWahl\Core\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;


/**
 * Repository for eligible voters.
 *
 * This only exists because otherwise Flow would not recognize the corresponding entity as an aggregate root
 * and automatically remove it if objects with a relation to it are removed.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Scope("singleton")
 */
class EligibleVoterRepository extends Repository {

	/**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * Returns the number of eligible voters for each value of the given discriminator.
	 *
	 * @param Election $election
	 * @param string $discriminatorIdentifier
	 * @return array
	 */
	public function countByDiscriminatorsValues(Election $election, $discriminatorIdentifier) {
		/** @var QueryBuilder $queryBuilder */
		$queryBuilder = $this->entityManager->createQueryBuilder();

		$queryBuilder
			->select('disc.value AS discriminator, COUNT(voter) AS cnt')
			->from('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', 'voter')
			->leftJoin('AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator', 'disc', Join::WITH, 'disc.voter = voter')
			->where('voter.election = :election AND disc.identifier = :discriminator')
			->groupBy('disc.value');

		$query = $queryBuilder->getQuery();
		$query->setParameters(array(
			'election' => $election,
			'discriminator' => $discriminatorIdentifier,
		));

		$result = $query->getResult();
		return $result;
	}

}
