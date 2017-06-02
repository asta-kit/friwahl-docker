<?php 
// necessary for the custom signal handler used here
declare(ticks = 10);

namespace AstaKit\FriWahl\BallotBoxBackend\Protocol;

/*                                                                                    *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.BallotBoxBackend".  *
 *                                                                                    *
 *                                                                                    */

use AstaKit\FriWahl\BallotBoxBackend\Domain\Model\Session;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\Exception\EndOfFileException;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\Exception\ProtocolError;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\Exception\QuitSessionException;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\Exception\SessionTerminatedException;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontend\AbstractCommand;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontend\Command;
use AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontend\ShowElectionsCommand;
use AstaKit\FriWahl\BallotBoxBackend\Session\SessionHandler;
use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use AstaKit\FriWahl\Core\Domain\Service\VotingService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\Logger;


/**
 * Protocol handler for the so-called "Urne Frontend" implementing the client-side functionality of FriWahl 1.
 *
 * This is a handler used to provide backwards compatibility to the old frontend implementation. By default it reads
 * the standard input and outputs the results to standard output, but this may be changed to
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class UrneFrontendProtocol_Original implements ProtocolHandler {

	/**
	 * @var BallotBox
	 */
	protected $ballotBox;

	/**
	 * @var VotingService
	 * @Flow\Inject
	 */
	protected $votingService;

	/**
	 * @var resource
	 */
	protected $inputStream;

	/**
	 * @var resource
	 */
	protected $outputStream;

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $log;

	/**
	 * @var SessionHandler
	 * @Flow\Inject
	 */
	protected $sessionHandler;

	/**
	 * The current ballot box session. Is regularly checked to see if it is still active.
	 *
	 * @var Session
	 */
	protected $session;

	/**
	 * The stream handler to handle input and output
	 *
	 * @var StreamHandler
	 */
	protected $ioHandler;

	/**
	 *
	 * Note: Flow will inject Doctrine\ORM\EntityManager here
	 *
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;


	/**
	 * @param BallotBox $ballotBox
	 * @param StreamHandler $streamHandler
	 */
	public function __construct(BallotBox $ballotBox, StreamHandler $streamHandler) {
		$this->ballotBox = $ballotBox;
		$this->ioHandler = $streamHandler;

		pcntl_signal(SIGUSR1, function($signal) {
			$this->handleSignal($signal);
		});
	}

	/**
	 * Runs a ballot box session according to the protocol defined for the legacy "FriWahl" client/server software.
	 *
	 * @return void
	 */
	public function run() {
		$this->session = $this->sessionHandler->startSessionForBallotBox($this->ballotBox);

		$keepAlive = TRUE;
		while ($keepAlive) {
			try {
				$line = trim($this->ioHandler->readLine());

				$this->checkBallotBoxAvailable();

				$this->log->log("Received line: " . $line, LOG_DEBUG);

				if ($line == '') {
					continue;
				}

				$this->handleCommand($line);

			} catch (ProtocolError $e) {
				// a generic error
				$this->ioHandler->writeLine(sprintf("-%d %s", $e->getCode(), $e->getMessage()));
			} catch (SessionTerminatedException $e) {
				$this->ioHandler->writeLine("-1023 " . $e->getMessage());
				$keepAlive = FALSE;
			} catch (QuitSessionException $e) {
				$keepAlive = FALSE;
			} catch (EndOfFileException $e) {
				$keepAlive = FALSE;
			} catch (\Exception $e) {
				$this->ioHandler->writeLine("-65533 " . $e->getMessage());
			}
		}

		$this->ioHandler->close();
	}

	/**
	 * Returns a command object
	 *
	 * @param string $commandName
	 * @return Command
	 * @throws \InvalidArgumentException
	 */
	protected function getCommandObject($commandName) {
		$commandClassName = str_replace(' ', '', ucwords(str_replace('-', ' ', $commandName))) . 'Command';
		$commandClassName = __NAMESPACE__ . '\\UrneFrontend\\' . $commandClassName;

		if (!class_exists($commandClassName)) {
			throw new \InvalidArgumentException('Unknown command ' . $commandName);
		}

		return new $commandClassName($this->ballotBox, $this->ioHandler);
	}

	/**
	 * @return void
	 * @throws ProtocolError
	 * @throws SessionTerminatedException
	 */
	protected function checkBallotBoxAvailable() {
		// check if our objects were changed from the outside world
		$this->entityManager->refresh($this->ballotBox);
		$this->entityManager->refresh($this->session);

		if (!$this->ballotBox->getElection()->isActive()) {
			throw new ProtocolError('Election inactive', ProtocolError::ERROR_BALLOTBOX_NOT_PERMITTED);
		}
		if (!$this->ballotBox->isAvailableForVotingSession()) {
			throw new ProtocolError('Ballot box locked', ProtocolError::ERROR_BALLOTBOX_NOT_PERMITTED);
		}
		if (!$this->session->isRunning()) {
			throw new SessionTerminatedException();
		}
	}

	/**
	 * @param $command
	 */
	protected function handleCommand($command) {
		$parameters = explode(' ', $command);
		$command = array_shift($parameters);

		/** @var AbstractCommand $commandHandler */
		$commandHandler = $this->getCommandObject($command);

		$commandHandler->process($parameters);

		$this->log->log('Command ' . $command . ' was processed', LOG_DEBUG);

		$this->ioHandler->writeLine("+OK");
		$commandHandler->printResult();
	}

	/**
	 * @param $signal
	 */
	public function handleSignal($signal) {
		if ($signal === SIGUSR1) {
			$this->log->log('Received SIGUSR1', LOG_DEBUG);

			$this->session->checkStatus();
		}
	}

}
namespace AstaKit\FriWahl\BallotBoxBackend\Protocol;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Protocol handler for the so-called "Urne Frontend" implementing the client-side functionality of FriWahl 1.
 * 
 * This is a handler used to provide backwards compatibility to the old frontend implementation. By default it reads
 * the standard input and outputs the results to standard output, but this may be changed to
 */
class UrneFrontendProtocol extends UrneFrontendProtocol_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 * @param BallotBox $ballotBox
	 * @param StreamHandler $streamHandler
	 */
	public function __construct() {
		$arguments = func_get_args();

		if (!array_key_exists(0, $arguments)) $arguments[0] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\Core\Domain\Model\BallotBox');
		if (!array_key_exists(1, $arguments)) $arguments[1] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\BallotBoxBackend\Protocol\StreamHandler');
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $ballotBox in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		if (!array_key_exists(1, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $streamHandler in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);
		if ('AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontendProtocol' === get_class($this)) {
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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontendProtocol');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontendProtocol', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontendProtocol', $propertyName, 'var');
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
		$this->votingService = new \AstaKit\FriWahl\Core\Domain\Service\VotingService();
		$log_reference = &$this->log;
		$this->log = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Log\SystemLoggerInterface');
		if ($this->log === NULL) {
			$this->log = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('6d57d95a1c3cd7528e3e6ea15012dac8', $log_reference);
			if ($this->log === NULL) {
				$this->log = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('6d57d95a1c3cd7528e3e6ea15012dac8',  $log_reference, 'TYPO3\Flow\Log\SystemLoggerInterface', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Log\SystemLoggerInterface'); });
			}
		}
		$sessionHandler_reference = &$this->sessionHandler;
		$this->sessionHandler = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('AstaKit\FriWahl\BallotBoxBackend\Session\SessionHandler');
		if ($this->sessionHandler === NULL) {
			$this->sessionHandler = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('b60d513075e4ce510f6ef459493c541c', $sessionHandler_reference);
			if ($this->sessionHandler === NULL) {
				$this->sessionHandler = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('b60d513075e4ce510f6ef459493c541c',  $sessionHandler_reference, 'AstaKit\FriWahl\BallotBoxBackend\Session\SessionHandler', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('AstaKit\FriWahl\BallotBoxBackend\Session\SessionHandler'); });
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
  0 => 'votingService',
  1 => 'log',
  2 => 'sessionHandler',
  3 => 'entityManager',
);
	}
}
#