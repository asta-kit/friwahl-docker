<?php 
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Security\Voting\VotingAccessManager;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Abstract base class for votings.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 * @ORM\Table(name="astakit_friwahl_core_domain_model_voting")
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class Voting_Original {

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $name;

	/**
	 * @var Election
	 * @ORM\ManyToOne(inversedBy="votings")
	 */
	protected $election;

	/**
	 * The discriminator used to determine if a voter may participate in this voting.
	 *
	 * @var string
	 */
	protected $discriminator = '';

	/**
	 * The values used to allow/deny voting based on the configured discrimination mode.
	 *
	 * @var array
	 */
	protected $discriminatorValues = array();

	/**
	 * If participation in this voting should be allowed or denied based on the discriminator values.
	 *
	 * @var integer
	 */
	protected $discriminationMode = self::DISCRIMINATION_MODE_ALLOW;

	const DISCRIMINATION_MODE_ALLOW = 1;
	const DISCRIMINATION_MODE_DENY = 2;

	/**
	 * The group this voting belongs to.
	 *
	 * If this is set, $election must be NULL (the relation to the election is implicitly defined via the group then).
	 *
	 * @var VotingGroup
	 * @ORM\ManyToOne(inversedBy="votings")
	 * @ORM\Column(name="votinggroup")
	 * NOTE: This field should be named $group, but currently Doctrine/Flow do not support naming a relation column
	 * different than the field (and "group" is a reserved SQL keyword).
	 */
	protected $votingGroup;

	/**
	 * @var VotingAccessManager
	 * @Flow\Inject
	 */
	protected $votingAccessManager;


	/**
	 * Constructor for a voting. Either the election or the voting group have to be set, but not both.
	 *
	 * @param string $name
	 * @param Election $election The election this voting belongs to
	 * @param VotingGroup $votingGroup The group this voting belongs to.
	 * @throws \RuntimeException
	 */
	public function __construct($name, Election $election = NULL, VotingGroup $votingGroup = NULL) {
		if ($election !== NULL && $votingGroup !== NULL) {
			throw new \RuntimeException('Cannot set both election and voting group for a voting.', 1403516216);
		}
		if ($election === NULL && $votingGroup === NULL) {
			throw new \RuntimeException('One of election and voting group has to be set for a voting.', 1403516217);
		}

		$this->votingGroup = $votingGroup;
		$this->election = $election;
		$this->name = $name;

		if ($election) {
			$this->election->addVoting($this);
		} else {
			$this->votingGroup->addVoting($this);
		}
	}

	/**
	 * Returns the type of this record. This is made abstract so that it's really clear for people writing new
	 * derived classes that this has to be implemented.
	 *
	 * @return string
	 */
	abstract public function getType();

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		if (!$this->election && $this->votingGroup) {
			return $this->votingGroup->getElection();
		}
		return $this->election;
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
	 * Checks if the given voter may participate (vote) in this voting. The check is deferred to the voting access
	 * manager, which holds instances of all defined voters.
	 *
	 * @param EligibleVoter $voter
	 * @return bool
	 */
	public function isAllowedToParticipate(EligibleVoter $voter) {
		return $this->votingAccessManager->mayParticipate($voter, $this);
	}

	/**
	 * @param string $discriminator
	 */
	public function setDiscriminator($discriminator) {
		$this->discriminator = $discriminator;
	}

	/**
	 * @return string
	 */
	public function getDiscriminator() {
		return $this->discriminator;
	}

	/**
	 * @param array $discriminatorValues
	 */
	public function setDiscriminatorValues($discriminatorValues) {
		$this->discriminatorValues = $discriminatorValues;
	}

	/**
	 * @return array
	 */
	public function getDiscriminatorValues() {
		return $this->discriminatorValues;
	}

	/**
	 * @param int $discriminationMode
	 */
	public function setDiscriminationMode($discriminationMode) {
		$this->discriminationMode = $discriminationMode;
	}

	/**
	 * @return int
	 */
	public function getDiscriminationMode() {
		return $this->discriminationMode;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\VotingGroup
	 */
	public function getGroup() {
		return $this->votingGroup;
	}

}
namespace AstaKit\FriWahl\Core\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Abstract base class for votings.
 * @\TYPO3\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\Table(name="astakit_friwahl_core_domain_model_voting")
 * @\Doctrine\ORM\Mapping\InheritanceType("SINGLE_TABLE")
 * @\TYPO3\Flow\Annotations\Entity
 * @\Doctrine\ORM\Mapping\Table(name="astakit_friwahl_core_domain_model_voting")
 * @\Doctrine\ORM\Mapping\InheritanceType("SINGLE_TABLE")
 */
abstract class Voting extends Voting_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface, \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface {

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
	 * @param string $name
	 * @param Election $election The election this voting belongs to
	 * @param VotingGroup $votingGroup The group this voting belongs to.
	 * @throws \RuntimeException
	 */
	public function __construct() {
		$arguments = func_get_args();

		$this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

			if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
		call_user_func_array('parent::__construct', $arguments);

			} else {
				$this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = TRUE;
				try {
				
					$methodArguments = array();

				if (array_key_exists(0, $arguments)) $methodArguments['name'] = $arguments[0];
				if (array_key_exists(1, $arguments)) $methodArguments['election'] = $arguments[1];
				if (array_key_exists(2, $arguments)) $methodArguments['votingGroup'] = $arguments[2];
			
				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['TYPO3\Flow\Aop\Advice\BeforeAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['TYPO3\Flow\Aop\Advice\BeforeAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Voting', '__construct', $methodArguments);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Voting', '__construct', $methodArguments);
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
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Voting', '__clone', $methodArguments);
					foreach ($advices as $advice) {
						$advice->invoke($joinPoint);
					}

					$methodArguments = $joinPoint->getMethodArguments();
				}

				$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Voting', '__clone', $methodArguments);
				$result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
				$methodArguments = $joinPoint->getMethodArguments();

				if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'])) {
					$advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['TYPO3\Flow\Aop\Advice\AfterReturningAdvice'];
					$joinPoint = new \TYPO3\Flow\Aop\JoinPoint($this, 'AstaKit\FriWahl\Core\Domain\Model\Voting', '__clone', $methodArguments, NULL, $result);
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
}
#