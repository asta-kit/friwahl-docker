<?php 
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Repository\BallotBoxRepository;
use AstaKit\FriWahl\Core\Domain\Repository\VoteRepository;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * A ballot box, used for collecting votes. Each instance of this class belongs to a physical ballot box.
 *
 * What is basically modelled here is a state machine, with different
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class BallotBox_Original {

	/**
	 * @var string
	 * @Flow\Identity
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 */
	protected $identifier;

	/**
	 * The name of this ballot box.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The group this ballot box belongs to.
	 *
	 * @var string
	 * @ORM\Column(name="boxgroup")
	 */
	protected $group = '';

	/**
	 * The election this ballot box belongs to.
	 *
	 * @var \AstaKit\FriWahl\Core\Domain\Model\Election
	 * @ORM\ManyToOne
	 */
	protected $election;

	/**
	 * The status of this ballot box.
	 *
	 * Possible state transitions are:
	 * - 0 -> 1, 10
	 * - 1 -> 2
	 * - 2 -> 3
	 * - 3 -> 2, 4
	 * - 4 -> 1, 5
	 * - 5 -> 6
	 * - 6 -> 7, 8, 9
	 * - 7 -> 6
	 *
	 * Start state is 0
	 * End states are 8, 9, 10
	 *
	 * @var integer
	 */
	protected $status = self::STATUS_NEW;

	/** Box has been created and still is in custody of the election committee */
	const STATUS_NEW = 0;
	/** Box has been handed out */
	const STATUS_EMITTED = 1;
	/** Currently open, new votes are accepted */
	const STATUS_OPENED = 2;
	/** Closed, may be reopened */
	const STATUS_CLOSED = 3;
	/** Ballot box has been returned to election committee */
	const STATUS_RETURNED = 4;
	/** Ballot box is currently being counted */
	const STATUS_COUNTING = 5;
	/** Ballot box was counted, awaiting confirmation of results */
	const STATUS_COUNTED = 6;
	/** Ballot box is being counted again */
	const STATUS_RECOUNTING = 7;
	/** Counted and results are valid */
	const STATUS_VALID = 8;
	/** Counted and results are invalid, i.e. the whole ballot box is void. Results won't be used for final election
	 * results */
	const STATUS_VOID = 9;
	/** Ballot box has not been used */
	const STATUS_UNUSED = 10;

	/**
	 * @var array
	 */
	protected static $statusTexts = array(
		self::STATUS_NEW => 'new',
		self::STATUS_EMITTED => 'emitted',
		/** Currently open, new votes are accepted */
		self::STATUS_OPENED => 'opened',
		/** Closed, may be reopened */
		self::STATUS_CLOSED => 'closed',
		/** Ballot box has been returned to election committee */
		self::STATUS_RETURNED => 'returned',
		/** Ballot box is currently being counted */
		self::STATUS_COUNTING => 'counting',
		/** Ballot box was counted, awaiting confirmation of results */
		self::STATUS_COUNTED => 'counted',
		/** Ballot box is being counted again */
		self::STATUS_RECOUNTING => 'recounting',
		/** Counted and results are valid */
		self::STATUS_VALID => 'valid',
		/** Counted and results are invalid, i.e. the whole ballot box is void. Results won't be used for final election
		 * results */
		self::STATUS_VOID => 'void',
		/** Ballot box has not been used */
		self::STATUS_UNUSED => 'unused',
	);

	/**
	 * @var BallotBoxRepository
	 * @Flow\Inject
	 */
	protected $ballotBoxRepository;

	/**
	 * @var VoteRepository
	 * @Flow\Inject
	 */
	protected $voteRepository;

	/**
	 * @var string
	 * @ORM\Column(length=1000, nullable=true)
	 */
	protected $sshPublicKey;


	/**
	 * @param string $identifier
	 * @param string $name
	 * @param Election $election
	 */
	public function __construct($identifier, $name, Election $election) {
		$this->identifier = $identifier;
		$this->name     = $name;
		$this->election = $election;
		$this->election->addBallotBox($this);
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
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
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param string $group
	 * @return void
	 */
	public function setGroup($group) {
		$this->group = $group;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		return $this->election;
	}

	/**
	 * Returns the status of this ballot box. See STATUS_* constants for possible values.
	 *
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns the status as a text.
	 *
	 * @return string
	 */
	public function getStatusText() {
		return self::$statusTexts[$this->status];
	}

	/**
	 * Returns the number of voters who have voted in this ballot box.
	 *
	 * @return int
	 */
	public function getVotersCount() {
		return $this->voteRepository->countVotersByBallotBox($this);
	}

	/**
	 * Returns the number of votes in the ballot box.
	 *
	 * @return int
	 */
	public function getVotesCount() {
		return $this->voteRepository->countByBallotBox($this);
	}

	/**
	 * @return Vote[]
	 */
	public function getQueuedVotes() {
		$pendingVotes = $this->ballotBoxRepository->findQueuedVotesForBallotBox($this);

		return $pendingVotes;
	}

	/**
	 * Returns the number of queued votes.
	 *
	 * @return int
	 */
	public function getQueuedVotesCount() {
		return $this->voteRepository->countQueuedByBallotBox($this);
	}

	/**
	 * Returns the number of committed votes.
	 * @return int
	 */
	public function getCommittedVotesCount() {
		return $this->voteRepository->countCommittedByBallotBox($this);
	}

	/**
	 * @param string $sshPublicKey
	 */
	public function setSshPublicKey($sshPublicKey) {
		$this->sshPublicKey = $sshPublicKey;
	}

	/**
	 * @return string
	 */
	public function getSshPublicKey() {
		return $this->sshPublicKey;
	}

	public function isNew() {
		return $this->status === self::STATUS_NEW;
	}

	public function isEmitted() {
		return $this->status === self::STATUS_EMITTED;
	}

	public function isReturned() {
		return $this->status === self::STATUS_RETURNED;
	}

	/**
	 * Emits this ballot box, making it available for voting sessions.
	 *
	 * @throws \RuntimeException
	 */
	public function emit() {
		if (!$this->isReadyToBeEmitted()) {
			throw new \RuntimeException('Cannot emit a box that is not new');
		}
		$this->status = self::STATUS_EMITTED;
	}

	/**
	 * Returns a box to the election committee, making it unavailable for voting sessions.
	 *
	 * This method should have been called return, but that is a reserved keyword in PHP and cannot be used as a method
	 * name.
	 *
	 * @throws \RuntimeException
	 */
	public function returnBox() {
		if (!$this->isAvailableForVotingSession()) {
			throw new \RuntimeException('Cannot return a box that is not available for voting.');
		}
		$this->status = self::STATUS_RETURNED;
	}

	/**
	 * Returns TRUE if this ballot box is open for a voting session, i.e. a voting session could be started.
	 * This also applies if there is an active voting session.
	 *
	 * @return bool
	 */
	public function isAvailableForVotingSession() {
		return in_array($this->status, array(self::STATUS_EMITTED, self::STATUS_CLOSED, self::STATUS_OPENED));
	}

	/**
	 * Returns TRUE if this ballot box can be emitted
	 *
	 * @return bool
	 */
	public function isReadyToBeEmitted() {
		return in_array($this->status, array(self::STATUS_NEW, self::STATUS_RETURNED));
	}

}
namespace AstaKit\FriWahl\Core\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A ballot box, used for collecting votes. Each instance of this class belongs to a physical ballot box.
 * 
 * What is basically modelled here is a state machine, with different
 * @\TYPO3\Flow\Annotations\Entity
 */
class BallotBox extends BallotBox_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface, \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface {

	private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

	private $Flow_Aop_Proxy_groupedAdviceChains = array();

	private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


	/**
	 * Autogenerated Proxy Method
	 * @param string $identifier
	 * @param string $name
	 * @param Election $election
	 */
	public function __construct() {
		$arguments = func_get_args();

		$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

		if (!array_key_exists(0, $arguments)) $arguments[0] = NULL;
		if (!array_key_exists(1, $arguments)) $arguments[1] = NULL;
		if (!array_key_exists(2, $arguments)) $arguments[2] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Model\Election');
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $identifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(1, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(2, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $election in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);
		if ('AstaKit\FriWahl\Core\Domain\Model\BallotBox' === get_class($this)) {
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
			'emit' => array(
				'TYPO3\Flow\Aop\Advice\AfterReturningAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\AfterReturningAdvice('AstaKit\FriWahl\Core\Domain\Aspect\BallotBoxLoggingAspect', 'afterEmitAdvice', $objectManager, NULL),
				),
			),
			'returnBox' => array(
				'TYPO3\Flow\Aop\Advice\AfterReturningAdvice' => array(
					new \TYPO3\Flow\Aop\Advice\AfterReturningAdvice('AstaKit\FriWahl\Core\Domain\Aspect\BallotBoxLoggingAspect', 'afterReturnAdvice', $objectManager, NULL),
				),
			),
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
	 * @throws \RuntimeException
	 */
	 public function emit() {

				// FIXME this can be removed again once Doctrine is fixed (see fixMethodsAndAdvicesArrayForDoctrineProxiesCode())
			$this->Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies();
		if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emit'])) {
		$result = parent::emit();

		} else {
			$this->Flow_Aop_Proxy_methodIsInAdviceMode['emit'] = TRUE;
			try {
			
					$methodArguments = array();

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', 'emit', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emit']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emit']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', 'emit', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

			} catch (\Exception $e) {
				unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emit']);
				throw $e;
			}
			unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emit']);
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 * @throws \RuntimeException
	 */
	 public function returnBox() {

				// FIXME this can be removed again once Doctrine is fixed (see fixMethodsAndAdvicesArrayForDoctrineProxiesCode())
			$this->Flow_Aop_Proxy_fixMethodsAndAdvicesArrayForDoctrineProxies();
		if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['returnBox'])) {
		$result = parent::returnBox();

		} else {
			$this->Flow_Aop_Proxy_methodIsInAdviceMode['returnBox'] = TRUE;
			try {
			
					$methodArguments = array();

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', 'returnBox', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['returnBox']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['returnBox']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', 'returnBox', $methodArguments, NULL, $result);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

			} catch (\Exception $e) {
				unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['returnBox']);
				throw $e;
			}
			unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['returnBox']);
		}
		return $result;
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

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', '__clone', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\BallotBox', '__clone', $methodArguments, NULL, $result);
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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\Core\Domain\Model\BallotBox');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\Core\Domain\Model\BallotBox', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\Core\Domain\Model\BallotBox', $propertyName, 'var');
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
		$ballotBoxRepository_reference = &$this->ballotBoxRepository;
		$this->ballotBoxRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Domain\Repository\BallotBoxRepository');
		if ($this->ballotBoxRepository === NULL) {
			$this->ballotBoxRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('c76ff19f21c0c7ffc21c85967095d815', $ballotBoxRepository_reference);
			if ($this->ballotBoxRepository === NULL) {
				$this->ballotBoxRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('c76ff19f21c0c7ffc21c85967095d815',  $ballotBoxRepository_reference, 'AstaKit\FriWahl\Core\Domain\Repository\BallotBoxRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Repository\BallotBoxRepository'); });
			}
		}
		$voteRepository_reference = &$this->voteRepository;
		$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository');
		if ($this->voteRepository === NULL) {
			$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('9e2dcff9598695fa5023e256eee7763e', $voteRepository_reference);
			if ($this->voteRepository === NULL) {
				$this->voteRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('9e2dcff9598695fa5023e256eee7763e',  $voteRepository_reference, 'AstaKit\FriWahl\Core\Domain\Repository\VoteRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Repository\VoteRepository'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'ballotBoxRepository',
  1 => 'voteRepository',
);
	}
}
#