<?php 
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use AstaKit\FriWahl\Core\Environment\SystemEnvironment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Model for an election.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class Election_Original implements VotingsContainer {

	/**
	 * The name of this election.
	 *
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 * @ORM\Column(unique=true)
	 */
	protected $name;

	/**
	 * An internal identifier for this election; used to e.g. name it for command line scripts. Keep this short and
	 * simple to save typing on the command line ;-)
	 *
	 * We use this as the identity column as it has to be unique and should not be changed afterwards.
	 *
	 * @var string
	 * NOTE: both the Identity and Id annotations are required because Doctrine needs Id to make the column the primary
	 *       key and use it as the internal identifier. The support for Identity in Flow is probably a bit broken
	 *       currently, as stated by Christian Müller at <http://www.typo3.net/forum/thematik/zeige/thema/115172/>
	 * @Flow\Identity
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 */
	protected $identifier;

	/**
	 * The date this election was created.
	 *
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * The periods during which this election is active, i.e. voting is possible.
	 *
	 * @var Collection<ElectionPeriod>
	 * @ORM\OneToMany(mappedBy="election", cascade={"remove", "persist"})
	 */
	protected $periods = array();

	/**
	 * @var Collection<BallotBox>
	 * @ORM\OneToMany(mappedBy="election")
	 */
	protected $ballotBoxes = array();

	/**
	 * If this is set, this election is treated as a test vote and can be used for the automated system tests.
	 * Otherwise most tests will
	 *
	 * @var bool
	 */
	protected $test = FALSE;

	/**
	 * The votings in this election.
	 *
	 * @var Collection<Voting>
	 * @ORM\OneToMany(mappedBy="election")
	 */
	protected $votings;

	/**
	 * @var SystemEnvironment
	 * @Flow\Inject
	 */
	protected $systemEnvironment;

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;


	/**
	 * @param string $name The name of this election.
	 * @param string $identifier The name of the identifier.
	 */
	public function __construct($name, $identifier) {
		$this->name = $name;
		$this->created = new \DateTime();
		$this->identifier = $identifier;

		$this->periods     = new ArrayCollection();
		$this->ballotBoxes = new ArrayCollection();
		$this->votings     = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param array $periods
	 */
	public function setPeriods($periods) {
		$this->periods = $periods;
	}

	/**
	 * Adds a period to this election.
	 *
	 * @param ElectionPeriod $period
	 */
	public function addPeriod(ElectionPeriod $period) {
		$this->periods->add($period);
	}

	/**
	 * @return array
	 */
	public function getPeriods() {
		return $this->periods;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $ballotBoxes
	 */
	public function setBallotBoxes($ballotBoxes) {
		$this->ballotBoxes = $ballotBoxes;
	}

	/**
	 * Adds a ballot box to this election.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function addBallotBox(BallotBox $ballotBox) {
		$this->ballotBoxes->add($ballotBox);
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getBallotBoxes() {
		return $this->ballotBoxes;
	}

	/**
	 * @param string $ballotBoxIdentifier
	 * @return bool
	 */
	public function hasBallotBox($ballotBoxIdentifier) {
		/** @var $ballotBox BallotBox */
		foreach ($this->ballotBoxes as $ballotBox) {
			if ($ballotBox->getIdentifier() === $ballotBoxIdentifier) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param boolean $test
	 */
	public function setTest($test) {
		$this->test = $test;
	}

	/**
	 * Returns TRUE if this election can be used as a test election, e.g. for manual or automated system tests.
	 *
	 * @return boolean
	 */
	public function isTest() {
		return $this->test;
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		$currentDate = $this->systemEnvironment->getCurrentDate();

		/** @var ElectionPeriod $period */
		foreach ($this->periods as $period) {
			if ($period->includes($currentDate)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	public function getVoterCount() {
		return $this->electionRepository->countVotersByElection($this);
	}

	/**
	 * Adds a voting to this election.
	 *
	 * @param Voting $voting
	 */
	public function addVoting(Voting $voting) {
		$this->votings->add($voting);
	}

	/**
	 * Returns all votings for this election.
	 *
	 * @return Collection<Voting>
	 */
	public function getVotings() {
		return $this->votings;
	}

}
namespace AstaKit\FriWahl\Core\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Model for an election.
 * @\TYPO3\Flow\Annotations\Entity
 */
class Election extends Election_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface, \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface {

	private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

	private $Flow_Aop_Proxy_groupedAdviceChains = array();

	private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 * @param string $name The name of this election.
	 * @param string $identifier The name of the identifier.
	 */
	public function __construct() {
		$arguments = func_get_args();

		$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

		if (!array_key_exists(0, $arguments)) $arguments[0] = NULL;
		if (!array_key_exists(1, $arguments)) $arguments[1] = NULL;
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(1, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $identifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);
		if ('AstaKit\FriWahl\Core\Domain\Model\Election' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray() {
		if (method_exists(get_parent_class($this), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

		$objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager;
		$this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
			'__clone' => array(
				'TYPO3\Flow\Aop\Advice\AfterReturningAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\AfterReturningAdvice('TYPO3\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
				),
			),
		);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {

		$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

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
		$result = NULL;
		if (method_exists(get_parent_class($this), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies() {
		if (!isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices) || empty($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices)) {
			$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
			if (is_callable('parent::Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies')) parent::Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies();
		}	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function Flow_Aop_Proxy_fixInjectedPropertiesForDoctrineProxies() {
		if (!$this instanceof \Doctrine\ORM\Proxy\Proxy || isset($this->Flow_Proxy_injectProperties_fixInjectedPropertiesForDoctrineProxies)) {
			return;
		}
		$this->Flow_Proxy_injectProperties_fixInjectedPropertiesForDoctrineProxies = TRUE;
		if (is_callable(array($this, 'Flow_Proxy_injectProperties'))) {
			$this->Flow_Proxy_injectProperties();
		}	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function Flow_Aop_Proxy_getAdviceChains($methodName) {
		$adviceChains = array();
		if (isset($this->Flow_Aop_Proxy_groupedAdviceChains[$methodName])) {
			$adviceChains = $this->Flow_Aop_Proxy_groupedAdviceChains[$methodName];
		} else {
			if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices[$methodName])) {
				$groupedAdvices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices[$methodName];
				if (isset($groupedAdvices['TYPO3\Flow\Aop\Advice\AroundAdvice'])) {
					$this->Flow_Aop_Proxy_groupedAdviceChains[$methodName]['TYPO3\Flow\Aop\Advice\AroundAdvice'] = new \TYPO3\Flow\Aop\Advice\AdviceChain($groupedAdvices['TYPO3\Flow\Aop\Advice\AroundAdvice']);
					$adviceChains = $this->Flow_Aop_Proxy_groupedAdviceChains[$methodName];
				}
			}
		}
		return $adviceChains;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function Flow_Aop_Proxy_invokeJoinPoint(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		if (__CLASS__ !== $joinPoint->getClassName()) return parent::Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
		if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode[$joinPoint->getMethodName()])) {
			return call_user_func_array(array('self', $joinPoint->getMethodName()), $joinPoint->getMethodArguments());
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __clone() {

				// FIXME this can be removed again once Doctrine is fixed (see fixMethodsAndAdvicesArrayForDoctrineProxiesCode())
			$this->Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies();
		if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
		$result = NULL;

		} else {
			$this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = TRUE;
			try {
			
					$methodArguments = array();

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Election', '__clone', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Election', '__clone', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

			} catch (\Exception $e) {
				unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
				throw $e;
			}
			unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Model\Election');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Model\Election', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Model\Election', $propertyName, 'var');
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
		$systemEnvironment_reference = &$this->systemEnvironment;
		$this->systemEnvironment = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Environment\SystemEnvironment');
		if ($this->systemEnvironment === NULL) {
			$this->systemEnvironment = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('68906434ec19d3aa7cec96666d10a344', $systemEnvironment_reference);
			if ($this->systemEnvironment === NULL) {
				$this->systemEnvironment = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('68906434ec19d3aa7cec96666d10a344',  $systemEnvironment_reference, 'AstaKit\FriWahl\Core\Environment\SystemEnvironment', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Environment\SystemEnvironment'); });
			}
		}
		$electionRepository_reference = &$this->electionRepository;
		$this->electionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository');
		if ($this->electionRepository === NULL) {
			$this->electionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('3602c402085a4c0a7a9f175df3c33216', $electionRepository_reference);
			if ($this->electionRepository === NULL) {
				$this->electionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('3602c402085a4c0a7a9f175df3c33216',  $electionRepository_reference, 'AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'systemEnvironment',
  1 => 'electionRepository',
);
	}
}
#