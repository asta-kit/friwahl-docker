<?php 
namespace AstaKit\FriWahl\BallotBoxBackend\Command;

/*                                                                                    *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.BallotBoxBackend".  *
 *                                                                                    *
 *                                                                                    */

use AstaKit\FriWahl\BallotBoxBackend\Domain\Model\Session;
use AstaKit\FriWahl\BallotBoxBackend\Domain\Model\UrneFrontendSession;
use AstaKit\FriWahl\BallotBoxBackend\Domain\Repository\SessionRepository;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\StandardInOutStreamHandler;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\StreamHandler;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontendProtocol;
use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;


/**
 * Command line controller for the ballot box backend.
 *
 * This is the central connecting part of the client-server system. Invoked by the SSH daemon, it hands the voting
 * session over to a protocol handler.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class BallotBoxSessionCommandController_Original extends CommandController {

	/**
	 * @var StreamHandler
	 * @Flow\Inject
	 */
	protected $streamHandler;

	/**
	 * @var SessionRepository
	 * @Flow\Inject
	 */
	protected $sessionRepository;

	/**
	 * Runs a voting session for a ballot box.
	 *
	 * @param BallotBox $ballotBox
	 * @return void
	 */
	public function sessionCommand(BallotBox $ballotBox) {
		$protocolHandler = new UrneFrontendProtocol($ballotBox, $this->streamHandler);
		$protocolHandler->run();
	}

	/**
	 * Prints the status of all ballot boxes.
	 *
	 * @param Election $election
	 * @return void
	 */
	public function statusCommand(Election $election) {
		$statuses = array();
		/** @var $ballotBox BallotBox */
		foreach ($election->getBallotBoxes() as $ballotBox) {
			if (!$ballotBox->isAvailableForVotingSession()) {
				continue;
			}

			/** @var Session $session */
			$session = $this->getActiveSessionForBallotBox($ballotBox);

			$boxInfo = array();
			if ($session) {
				$boxInfo['status'] = 'online';
				$boxInfo['started'] = $session->getDateStarted()->format('Y-m-d H:i');
				if ($session instanceof UrneFrontendSession) {
					$boxInfo['pid'] = $session->getPid();
				}
				$pendingVotes = $ballotBox->getQueuedVotesCount();
				$boxInfo['votesPending'] = $pendingVotes;
			} else {
				$boxInfo['status'] = 'offline';
				$boxInfo['started'] = '-';
				$boxInfo['pid'] = '-';
				$pendingVotes = 0;
				$boxInfo['votesPending'] = '-';
			}
			$committedVotes = $ballotBox->getCommittedVotesCount();
			$totalVotes = $pendingVotes + $committedVotes;
			$boxInfo['votesCommitted'] = $committedVotes;
			$boxInfo['votesTotal'] = $totalVotes;

			$statuses[$ballotBox->getIdentifier()] = $boxInfo;
		}

		ksort($statuses);

		$this->outputLine(str_pad('', 40, ' ', STR_PAD_BOTH)           . ' |  Session |                  |       |           Votes               |');
		$this->outputLine(str_pad('Ballot box', 40, ' ', STR_PAD_BOTH) . ' |  Status  |   Date started   |  PID  | Pending | Committed |  Total  |');
		$this->outputLine(str_pad('', 40, '-')                         . '-+----------+------------------+-------+---------+-----------+---------+');
		if (count($statuses) == 0) {
			$this->outputLine(' No ballot box available for voting.');
		}
		foreach ($statuses as $ballotBoxIdentifier => $statusInfo) {
			$dateStarted = $statusInfo['started'];
			$pid = $statusInfo['pid'];
			$this->outputLine(
				str_pad($ballotBoxIdentifier, 40, ' ', STR_PAD_RIGHT) . ' | '
				. str_pad($statusInfo['status'], 8, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($dateStarted, 16, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($pid, 5, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($statusInfo['votesPending'], 7, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($statusInfo['votesCommitted'], 9, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($statusInfo['votesTotal'], 7, ' ', STR_PAD_LEFT) . ' | '
			);
		}
	}

	/**
	 * Returns the active session for the current ballot box.
	 *
	 * @param BallotBox $ballotBox
	 * @return Session|null
	 */
	protected function getActiveSessionForBallotBox(BallotBox $ballotBox) {
		$sessions = $this->sessionRepository->findByBallotBox($ballotBox);

		/** @var $session Session */
		foreach ($sessions as $session) {
			if ($session->isRunning()) {
				return $session;
			}
		}
		return NULL;
	}

}
namespace AstaKit\FriWahl\BallotBoxBackend\Command;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Command line controller for the ballot box backend.
 * 
 * This is the central connecting part of the client-server system. Invoked by the SSH daemon, it hands the voting
 * session over to a protocol handler.
 */
class BallotBoxSessionCommandController extends BallotBoxSessionCommandController_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		parent::__construct();
		if ('AstaKit\FriWahl\BallotBoxBackend\Command\BallotBoxSessionCommandController' === get_class($this)) {
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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\BallotBoxBackend\Command\BallotBoxSessionCommandController');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\BallotBoxBackend\Command\BallotBoxSessionCommandController', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\BallotBoxBackend\Command\BallotBoxSessionCommandController', $propertyName, 'var');
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
		$this->injectReflectionService(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService'));
		$this->streamHandler = new \AstaKit\FriWahl\BallotBoxBackend\Protocol\StandardInOutStreamHandler();
		$sessionRepository_reference = &$this->sessionRepository;
		$this->sessionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\BallotBoxBackend\Domain\Repository\SessionRepository');
		if ($this->sessionRepository === NULL) {
			$this->sessionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('bb0d8972df0f8c7f4775bb0949fa2a69', $sessionRepository_reference);
			if ($this->sessionRepository === NULL) {
				$this->sessionRepository = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('bb0d8972df0f8c7f4775bb0949fa2a69',  $sessionRepository_reference, 'AstaKit\FriWahl\BallotBoxBackend\Domain\Repository\SessionRepository', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\BallotBoxBackend\Domain\Repository\SessionRepository'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'reflectionService',
  1 => 'streamHandler',
  2 => 'sessionRepository',
);
	}
}
#