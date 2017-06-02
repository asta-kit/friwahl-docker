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
class ElectionRepository_Original extends Repository {

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
namespace AstaKit\FriWahl\Core\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Repository for elections and the voters in an election.
 * 
 * The voters are part of this repository because they do not really form an aggregate root (according to the principles
 * of Domain Driven Design) – they are tied to their election and wouldn't exist without it.
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class ElectionRepository extends ElectionRepository_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository', $this);
		parent::__construct();
		if ('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository', $this);

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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository', $propertyName, 'var');
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
		$entityManager_reference = &$this->entityManager;
		$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('Doctrine\Common\Persistence\ObjectManager');
		if ($this->entityManager === NULL) {
			$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('ea59127cf49656654065ffe160cf78e1', $entityManager_reference);
			if ($this->entityManager === NULL) {
				$this->entityManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('ea59127cf49656654065ffe160cf78e1',  $entityManager_reference, 'Doctrine\Common\Persistence\ObjectManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\Common\Persistence\ObjectManager'); });
			}
		}
		$persistenceManager_reference = &$this->persistenceManager;
		$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		if ($this->persistenceManager === NULL) {
			$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('f1bc82ad47156d95485678e33f27c110', $persistenceManager_reference);
			if ($this->persistenceManager === NULL) {
				$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('f1bc82ad47156d95485678e33f27c110',  $persistenceManager_reference, 'TYPO3\Flow\Persistence\Doctrine\PersistenceManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'entityManager',
  1 => 'persistenceManager',
);
	}
}
#