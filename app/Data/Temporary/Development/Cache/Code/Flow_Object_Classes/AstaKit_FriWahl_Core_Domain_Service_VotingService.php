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
class VotingService_Original {

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
 namespace AstaKit\FriWahl\Core\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Service for handling the participation of a voter in votings (i.e. the act of voting itself, the essence of this
 * software).
 * 
 * Note that this class relies on Doctrine being used as the persistence layer – if another persistence layer is used,
 * this class has to be adjusted
 */
class VotingService extends VotingService_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if ('AstaKit\FriWahl\Core\Domain\Service\VotingService' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {

	if (property_exists($this, 'Flow_Persistence_RelatedEntities') && is_array($this->Flow_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		foreach ($this->Flow_Persistence_RelatedEntities as $entityInformation) {
			$entity = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType'], TRUE);
			if (isset($entityInformation['entityPath'])) {
				$this->$entityInformation['propertyName'] = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$entityInformation['propertyName'], $entityInformation['entityPath'], $entity);
			} else {
				$this->$entityInformation['propertyName'] = $entity;
			}
		}
		unset($this->Flow_Persistence_RelatedEntities);
	}
				$this->Flow_Proxy_injectProperties();
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Service\VotingService');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Service\VotingService', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
		if (is_array($this->$propertyName) || (is_object($this->$propertyName) && ($this->$propertyName instanceof \ArrayObject || $this->$propertyName instanceof \SplObjectStorage ||$this->$propertyName instanceof \Doctrine\Common\Collections\Collection))) {
			if (count($this->$propertyName) > 0) {
				foreach ($this->$propertyName as $key => $value) {
					$this->searchForEntitiesAndStoreIdentifierArray((string)$key, $value, $propertyName);
				}
			}
		}
		if (is_object($this->$propertyName) && !$this->$propertyName instanceof \Doctrine\Common\Collections\Collection) {
			if ($this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($this->$propertyName);
			} else {
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Service\VotingService', $propertyName, 'var');
				if (count($varTagValues) > 0) {
					$className = trim($varTagValues[0], '\\');
				}
				if (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->isRegistered($className) === FALSE) {
					$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($this->$propertyName));
				}
			}
			if ($this->$propertyName instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($this->$propertyName) || $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
					$this->Flow_Persistence_RelatedEntities = array();
					$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
				}
				$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($this->$propertyName);
				if (!$identifier && $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
					$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($this->$propertyName, '_identifier', TRUE));
				}
				$this->Flow_Persistence_RelatedEntities[$propertyName] = array(
					'propertyName' => $propertyName,
					'entityType' => $className,
					'identifier' => $identifier
				);
				continue;
			}
			if ($className !== FALSE && (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getScope($className) === \TYPO3\Flow\Object\Configuration\Configuration::SCOPE_SINGLETON || $className === 'TYPO3\Flow\Object\DependencyInjection\DependencyProxy')) {
				continue;
			}
		}
		$this->Flow_Object_PropertiesToSerialize[] = $propertyName;
	}
	$result = $this->Flow_Object_PropertiesToSerialize;
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function searchForEntitiesAndStoreIdentifierArray($path, $propertyValue, $originalPropertyName) {

		if (is_array($propertyValue) || (is_object($propertyValue) && ($propertyValue instanceof \ArrayObject || $propertyValue instanceof \SplObjectStorage))) {
			foreach ($propertyValue as $key => $value) {
				$this->searchForEntitiesAndStoreIdentifierArray($path . '.' . $key, $value, $originalPropertyName);
			}
		} elseif ($propertyValue instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($propertyValue) || $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
			if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
				$this->Flow_Persistence_RelatedEntities = array();
				$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
			}
			if ($propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($propertyValue);
			} else {
				$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($propertyValue));
			}
			$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($propertyValue);
			if (!$identifier && $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($propertyValue, '_identifier', TRUE));
			}
			$this->Flow_Persistence_RelatedEntities[$originalPropertyName . '.' . $path] = array(
				'propertyName' => $originalPropertyName,
				'entityType' => $className,
				'identifier' => $identifier,
				'entityPath' => $path
			);
			$this->$originalPropertyName = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$originalPropertyName, $path, NULL);
		}
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function Flow_Proxy_injectProperties() {
		$persistenceManager_reference = &$this->persistenceManager;
		$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		if ($this->persistenceManager === NULL) {
			$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('f1bc82ad47156d95485678e33f27c110', $persistenceManager_reference);
			if ($this->persistenceManager === NULL) {
				$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('f1bc82ad47156d95485678e33f27c110',  $persistenceManager_reference, 'TYPO3\Flow\Persistence\Doctrine\PersistenceManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface'); });
			}
		}
		$entityManager_reference = &$this->entityManager;
		$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('Doctrine\Common\Persistence\ObjectManager');
		if ($this->entityManager === NULL) {
			$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('ea59127cf49656654065ffe160cf78e1', $entityManager_reference);
			if ($this->entityManager === NULL) {
				$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('ea59127cf49656654065ffe160cf78e1',  $entityManager_reference, 'Doctrine\Common\Persistence\ObjectManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\Common\Persistence\ObjectManager'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'entityManager',
);
	}
}
#