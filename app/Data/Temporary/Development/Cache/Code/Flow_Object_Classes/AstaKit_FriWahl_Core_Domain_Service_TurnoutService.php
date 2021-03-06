<?php 
namespace AstaKit\FriWahl\Core\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\VotingTurnout;
use AstaKit\FriWahl\Core\Domain\Repository\EligibleVoterRepository;
use AstaKit\FriWahl\Core\Domain\Repository\VoteRepository;
use TYPO3\Flow\Annotations as Flow;


/**
 * Service for getting the voting participation.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class TurnoutService_Original {

	/**
	 * @var VoteRepository
	 * @Flow\Inject
	 */
	protected $voteRepository;

	/**
	 * @var EligibleVoterRepository
	 * @Flow\Inject
	 */
	protected $voterRepository;


	/**
	 * @param Voting $voting
	 */
	public function getTurnoutForVoting(Voting $voting) {
		$turnout = array(
			'_all' => array(
				'votes' => 0,
				'voters' => 0,
			)
		);

		if ($voting->getDiscriminator() !== '') {
			// participation is limited to certain discriminator values – we get a result per discriminator value
			$castedVotes = $this->voteRepository->countByDiscriminatorValuesForVoting($voting);
			$voterCounts = $this->voterRepository->countByDiscriminatorsValues($voting->getElection(), $voting->getDiscriminator());

			// TODO this currently does not support discrimination mode "DENY"
			$relevantDiscriminatorValues = $voting->getDiscriminatorValues();

			$this->fillTurnoutArrayWithZeroes($relevantDiscriminatorValues, $turnout);
			$this->mergeVoteCountsToTurnout($castedVotes, $turnout);
			$this->mergeVoterCountsToTurnout($voterCounts, $turnout);
		} else {
			// everybody may participate – we get a single result
			$castedVotes = $this->voteRepository->countByVoting($voting);
			$voterCount = $this->voterRepository->countByElection($voting->getElection());

			$turnout['_all']['votes'] = $castedVotes;
			$turnout['_all']['voters'] = $voterCount;
		}

		$turnout = new VotingTurnout($voting, $turnout);
		return $turnout;
	}

	/**
	 * @param array $relevantDiscriminatorValues
	 * @param array $turnout
	 * @return mixed
	 */
	private function fillTurnoutArrayWithZeroes($relevantDiscriminatorValues, &$turnout) {
		foreach ($relevantDiscriminatorValues as $discriminator) {
			$turnout[$discriminator] = array(
				'votes' => 0,
				'voters' => 0,
			);
		}
	}

	/**
	 * Merges the vote counts returned by the vote repository to the turnout array.
	 *
	 * @param array $castedVotes
	 * @param array $turnout
	 * @return int The total number of votes
	 */
	private function mergeVoteCountsToTurnout($castedVotes, &$turnout) {
		$totalVoteCount = 0;

		foreach ($castedVotes as $votes) {
			$discriminator = $votes['discriminator'];
			if (!in_array($discriminator, array_keys($turnout))) {
				continue;
			}

			$turnout[$discriminator]['votes'] = $votes['cnt'];
			$totalVoteCount += $votes['cnt'];
		}

		$turnout['_all']['votes'] = $totalVoteCount;
	}

	private function mergeVoterCountsToTurnout($voterCounts, &$turnout) {
		$totalVoterCount = 0;

		foreach ($voterCounts as $votes) {
			$discriminator = $votes['discriminator'];
			if (!in_array($discriminator, array_keys($turnout))) {
				continue;
			}

			$turnout[$discriminator]['voters'] = $votes['cnt'];
			$totalVoterCount += $votes['cnt'];
		}

		$turnout['_all']['voters'] = $totalVoterCount;
	}

}
 namespace AstaKit\FriWahl\Core\Domain\Service;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Service for getting the voting participation.
 */
class TurnoutService extends TurnoutService_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if ('AstaKit\FriWahl\Core\Domain\Service\TurnoutService' === get_class($this)) {
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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Service\TurnoutService');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Service\TurnoutService', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Service\TurnoutService', $propertyName, 'var');
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
		$voteRepository_reference = &$this->voteRepository;
		$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository');
		if ($this->voteRepository === NULL) {
			$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('9e2dcff9598695fa5023e256eee7763e', $voteRepository_reference);
			if ($this->voteRepository === NULL) {
				$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('9e2dcff9598695fa5023e256eee7763e',  $voteRepository_reference, 'AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository'); });
			}
		}
		$voterRepository_reference = &$this->voterRepository;
		$this->voterRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Domain\Repository\EligibleVoterRepository');
		if ($this->voterRepository === NULL) {
			$this->voterRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('9184d0101f642c6675cd000853a9a249', $voterRepository_reference);
			if ($this->voterRepository === NULL) {
				$this->voterRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('9184d0101f642c6675cd000853a9a249',  $voterRepository_reference, 'AstaKit\FriWahl\Core\Domain\Repository\EligibleVoterRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Repository\EligibleVoterRepository'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'voteRepository',
  1 => 'voterRepository',
);
	}
}
#