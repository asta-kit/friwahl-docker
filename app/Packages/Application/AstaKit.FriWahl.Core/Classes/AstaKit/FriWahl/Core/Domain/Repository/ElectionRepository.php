<?php
namespace AstaKit\FriWahl\Core\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\QueryResultInterface;
use TYPO3\Flow\Persistence\Repository;

/**
 * Repository for elections and the voters in an election.
 *
 * The voters are part of this repository because they do not really form an aggregate root (according to the principles
 * of Domain Driven Design) – they are tied to their election and wouldn't exist without it.
 *
 * @Flow\Scope("singleton")
 */
class ElectionRepository extends Repository {

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;

	/**
	 * Creates a query for voters.
	 *
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 */
	public function createVotersQuery() {
		// we cannot add the constraint for the election here because $query->matching() always overwrites
		// existing constraints
		$query = $this->persistenceManager->createQueryForType('AstaKit\\FriWahl\\Core\\Domain\\Model\\EligibleVoter');
		return $query;
	}

	/**
	 * Counts the voters for a given election.
	 *
	 * @param Election $election
	 * @return int
	 */
	public function countVotersByElection(Election $election) {
		$query = $this->createVotersQuery();

		return $query->matching(
			$query->equals('election', $election)
		)->count();
	}

	/**
	 * Returns all voters available for the given election
	 *
	 * @param Election $election
	 * @return QueryResultInterface
	 */
	public function findVotersByElection(Election $election) {
		$query = $this->createVotersQuery();

		return $query->matching(
			$query->equals('election', $election)
		)->execute();
	}

	/**
	 * Finds voters by the given criteria.
	 *
	 * @param Election $election
	 * @param array $criteria Array with criteria, with the fields to search as key-value pairs in the "fields" sub-array
	 * @return QueryResultInterface
	 *
	 * @throws \Exception If no criteria are defined
	 */
	public function findVotersByCriteria(Election $election, $criteria) {
		$queryParameters = $queryParts = array();
		if (isset($criteria['fields']['givenName']) && $criteria['fields']['givenName'] != '') {
			$queryParameters['givenName'] = $criteria['fields']['givenName'] . '%';
			$queryParts[] = 'v.givenName LIKE :givenName';
		}
		if (isset($criteria['fields']['familyName']) && $criteria['fields']['familyName'] != '') {
			$queryParameters['familyName'] = $criteria['fields']['familyName'] . '%';
			$queryParts[] = 'v.familyName LIKE :familyName';
		}
		if (count($queryParts) == 0) {
			throw new \Exception('No criteria given for voter search', 1401630546);
		}
		$queryParameters['election'] = $election;

		$dql = 'SELECT v FROM AstaKit\FriWahl\Core\Domain\Model\EligibleVoter v WHERE '
			. ' v.election = :election AND '
			. implode(' AND ', $queryParts);

		// TODO implement support for discriminators

		/** @var $query \Doctrine\ORM\Query */
		$query = $this->entityManager->createQuery($dql);
		$query->setParameters($queryParameters);

		return $query->execute();
	}

	public function findVotersByDiscriminator(Election $election, $discriminator, $discriminatorValue) {
		$dql = 'SELECT v
			FROM AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator d
			JOIN AstaKit\FriWahl\Core\Domain\Model\EligibleVoter v
			WHERE d.voter = v AND d.identifier = :identifier AND d.value = :value AND v.election = :election';

		/** @var $query \Doctrine\ORM\Query */
		$query = $this->entityManager->createQuery($dql);

		return $query->execute(array(
			'election' => $election,
			'identifier' => $discriminator,
			'value' => $discriminatorValue
		));
	}

	public function findOneVoterByDiscriminator(Election $election, $discriminator, $discriminatorValue) {
		$result = $this->findVotersByDiscriminator($election, $discriminator, $discriminatorValue);

		if (!$result) {
			return null;
		} else {
			return $result[0];
		}
	}

}
