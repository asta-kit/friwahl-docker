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
class VoteRepository_Original extends Repository {

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
namespace AstaKit\FriWahl\Core\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Repository for votes.
 * 
 * This only exists because otherwise Flow would not recognize the corresponding entity as an aggregate root
 * and automatically remove it if objects with a relation to it are removed.
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class VoteRepository extends VoteRepository_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'AstaKit\FriWahl\Core\Domain\Repository\VoteRepository') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', $this);
		parent::__construct();
		if ('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'AstaKit\FriWahl\Core\Domain\Repository\VoteRepository') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', $this);

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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', $propertyName, 'var');
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