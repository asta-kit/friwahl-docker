<?php 
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A person who is able to vote in an election. The right to vote might be limited to certain votings, or a person
 * might be excluded from a voting for several reasons.
 *
 * In addition to the name, several configurable discriminators can be stored with a voter. These can be used to record
 * e.g. the group of voters a person belongs to, the matriculation number, the department where they can vote, or
 * their sex or nationality (to realize sex/nationality based votings for minority representations).
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class EligibleVoter_Original {

	/**
	 * The voter's given name.
	 *
	 * @var string
	 * @ORM\Column(length=40)
	 */
	protected $givenName;

	/**
	 * The voter's family name.
	 *
	 * @var string
	 * @ORM\Column(length=40)
	 */
	protected $familyName;

	/**
	 * Special properties of this user, e.g. the matriculation number, sex, nationality, field of study or department.
	 *
	 * @var Collection<\AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator>
	 * @ORM\OneToMany(mappedBy="voter", indexBy="identifier", cascade={"persist", "remove"})
	 */
	protected $discriminators;

	/**
	 * The election this voter belongs to.
	 *
	 * @var Election
	 * @ORM\ManyToOne(inversedBy="voter")
	 */
	protected $election;

	/**
	 * The votes this voter has cast. This collection is read-only in this object, as new objects can only be created
	 * by a stored procedure.
	 *
	 * @var Collection<Vote>
	 * @ORM\OneToMany(mappedBy="voter", cascade={}, orphanRemoval=false)
	 */
	protected $votes;


	/**
	 * @param Election $election
	 * @param string $givenName
	 * @param string $familyName
	 */
	public function __construct($election, $givenName, $familyName) {
		$this->givenName = $givenName;
		$this->familyName = $familyName;

		$this->discriminators = new ArrayCollection();
		$this->votes = new ArrayCollection();

		$this->election = $election;
	}

	/**
	 * @return string
	 */
	public function getGivenName() {
		return $this->givenName;
	}

	/**
	 * @return string
	 */
	public function getFamilyName() {
		return $this->familyName;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->givenName . ' ' . $this->familyName;
	}

	/**
	 * Returns the election-unique identifier for this voter.
	 *
	 * @return string
	 */
	public function getIdentifier() {
		$letters = mb_substr($this->getGivenName(), 0, 1, 'UTF-8')
			. mb_substr($this->getFamilyName(), -1, 1, 'UTF-8');

		$letters = strtr($letters,
		array(
			'Ä' => 'A',
			'ä' => 'a',
			'Á' => 'A',
			'á' => 'a',
			'À' => 'A',
			'à' => 'a',
			'Ö' => 'O',
			'ö' => 'o',
			'Ó' => 'O',
			'ó' => 'o',
			'Ò' => 'O',
			'ò' => 'o',
			'Ü' => 'u',
			'ü' => 'u',
			'ß' => 's',
			'Ç' => 'C',
			'ç' => 'c',
			'È' => 'E',
			'è' => 'e',
			'É' => 'E',
			'é' => 'e',
			'Ø' => 'O',
			'ø' => 'o',
			'Æ' => 'A',
			'æ' => 'a',
		));

		$letters = strtoupper($letters);

		return $this->getDiscriminator('matriculationNumber')->getValue() . $letters;
	}

	/**
	 * Returns the election this voter belongs to.
	 *
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		return $this->election;
	}

	/**
	 * Adds a discriminator for this voter.
	 *
	 * @param string $identifier
	 * @param string $value
	 */
	public function addDiscriminator($identifier, $value) {
		$discriminator = new VoterDiscriminator($this, $identifier, $value);

		$this->discriminators->set($identifier, $discriminator);
	}

	/**
	 * Returns the discriminator with the given identifier, if present.
	 *
	 * @param string $identifier
	 * @return VoterDiscriminator|null
	 */
	public function getDiscriminator($identifier) {
		/**
		 * We use the Doctrine feature of using a property of the object as the key for the collection (indexBy column
		 * property).
		 * However, this currently (2014-05) is not supported natively by Flow and requires a custom patch. See
		 * http://forge.typo3.org/issues/44740 for its current status.
		 *
		 * See the test getDiscriminatorReturnsCorrectObjectAfterVoterHasBeenPersistedAndRestored()
		 */
		return $this->discriminators->get($identifier);
	}

	/**
	 * Checks if this voter has a discriminator with the given identifier.
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function hasDiscriminator($identifier) {
		return $this->getDiscriminator($identifier) !== NULL;
	}

	/**
	 * The discriminators for this voter.
	 *
	 * @return \Doctrine\Common\Collections\Collection<Discriminator>
	 */
	public function getDiscriminators() {
		return $this->discriminators;
	}

	/**
	 * Returns the list of votings this voter might participate in.
	 *
	 * @return Voting[]
	 */
	public function getVotings() {
		$allowedVotings = array();

		/** @var Voting $voting */
		foreach ($this->election->getVotings() as $voting) {
			if ($voting->isAllowedToParticipate($this)) {
				$allowedVotings[] = $voting;
			}
		}
		return $allowedVotings;
	}

	/**
	 * Returns the votes this voter has cast.
	 *
	 * This is manually updated by the voting service when creating new votes;
	 *
	 * @return Vote[]
	 */
	public function getVotes() {
		return $this->votes;
	}

	/**
	 * Adds a new vote for this voter.
	 *
	 * This is a strictly internal method only used by the VotingService.
	 * NEVER add a new vote object through this method.
	 *
	 * @param Vote $vote
	 * @return void
	 */
	public function addVote(Vote $vote) {
		$this->votes[] = $vote;
	}

	/**
	 * Removes pending votes from the voter object.
	 *
	 * This is a strictly internal method only used by the VotingService.
	 * NEVER call this manually unless you know what you're doing!
	 *
	 * @return int The number of removed vote objects
	 */
	public function removePendingVotes() {
		$removedVotes = 0;
		foreach ($this->votes as $vote) {
			/** @var $vote Vote */
			if ($vote->isQueued()) {
				$this->votes->removeElement($vote);
				++$removedVotes;
			}
		}
	}

}
namespace AstaKit\FriWahl\Core\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A person who is able to vote in an election. The right to vote might be limited to certain votings, or a person
 * might be excluded from a voting for several reasons.
 * 
 * In addition to the name, several configurable discriminators can be stored with a voter. These can be used to record
 * e.g. the group of voters a person belongs to, the matriculation number, the department where they can vote, or
 * their sex or nationality (to realize sex/nationality based votings for minority representations).
 * @\TYPO3\Flow\Annotations\Entity
 */
class EligibleVoter extends EligibleVoter_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface, \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 * introduced by TYPO3\Flow\Persistence\Aspect\PersistenceMagicAspect
	 */
	protected $Persistence_Object_Identifier = NULL;

	private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

	private $Flow_Aop_Proxy_groupedAdviceChains = array();

	private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 * @param Election $election
	 * @param string $givenName
	 * @param string $familyName
	 */
	public function __construct() {
		$arguments = func_get_args();

		$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

			if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

		if (!array_key_exists(0, $arguments)) $arguments[0] = NULL;
		if (!array_key_exists(1, $arguments)) $arguments[1] = NULL;
		if (!array_key_exists(2, $arguments)) $arguments[2] = NULL;
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $election in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(1, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $givenName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(2, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $familyName in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);

			} else {
				$this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = TRUE;
				try {
				
					$methodArguments = array();

				if (array_key_exists(0, $arguments)) $methodArguments['election'] = $arguments[0];
				if (array_key_exists(1, $arguments)) $methodArguments['givenName'] = $arguments[1];
				if (array_key_exists(2, $arguments)) $methodArguments['familyName'] = $arguments[2];
			
				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['TYPO3\Flow\Aop\Advice\BeforeAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['TYPO3\Flow\Aop\Advice\BeforeAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', '__construct', $methodArguments);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', '__construct', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				} catch (\Exception $e) {
					unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
					throw $e;
				}
				unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
				return;
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
				'TYPO3\Flow\Aop\Advice\BeforeAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\BeforeAdvice('TYPO3\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
				),
				'TYPO3\Flow\Aop\Advice\AfterReturningAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\AfterReturningAdvice('TYPO3\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
				),
			),
			'__construct' => array(
				'TYPO3\Flow\Aop\Advice\BeforeAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\BeforeAdvice('TYPO3\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
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

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\BeforeAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\BeforeAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', '__clone', $methodArguments);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', '__clone', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', '__clone', $methodArguments, NULL, $result);
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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', $propertyName, 'var');
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
}
#