<?php 
namespace TYPO3\Flow\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandManager;

/**
 * A Command Controller which provides help for available commands
 *
 * @Flow\Scope("singleton")
 */
class HelpCommandController_Original extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var CommandManager
	 */
	protected $commandManager;

	/**
	 * @param \TYPO3\Flow\Package\PackageManagerInterface $packageManager
	 * @return void
	 */
	public function injectPackageManager(\TYPO3\Flow\Package\PackageManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 * @return void
	 */
	public function injectBootstrap(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * @param CommandManager $commandManager
	 * @return void
	 */
	public function injectCommandManager(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Displays a short, general help message
	 *
	 * This only outputs the Flow version number, context and some hint about how to
	 * get more help about commands.
	 *
	 * @return void
	 * @Flow\Internal
	 */
	public function helpStubCommand() {
		$context = $this->bootstrap->getContext();

		$this->outputLine('<b>TYPO3 Flow %s ("%s" context)</b>', array($this->packageManager->getPackage('TYPO3.Flow')->getPackageMetaData()->getVersion() ?: FLOW_VERSION_BRANCH, $context));
		$this->outputLine('<i>usage: %s <command identifier></i>', array($this->getFlowInvocationString()));
		$this->outputLine();
		$this->outputLine('See "%s help" for a list of all available commands.', array($this->getFlowInvocationString()));
		$this->outputLine();
	}

	/**
	 * Display help for a command
	 *
	 * The help command displays help for a given command:
	 * ./flow help <commandIdentifier>
	 *
	 * @param string $commandIdentifier Identifier of a command for more details
	 * @return void
	 */
	public function helpCommand($commandIdentifier = NULL) {
		$exceedingArguments = $this->request->getExceedingArguments();
		if (count($exceedingArguments) > 0 && $commandIdentifier === NULL) {
			$commandIdentifier = $exceedingArguments[0];
		}

		if ($commandIdentifier === NULL) {
			$this->displayHelpIndex();
		} else {
			$matchingCommands = $this->commandManager->getCommandsByIdentifier($commandIdentifier);
			$numberOfMatchingCommands = count($matchingCommands);
			if ($numberOfMatchingCommands === 0) {
				$this->outputLine('No command could be found that matches the command identifier "%s".', array($commandIdentifier));
			} elseif ($numberOfMatchingCommands > 1) {
				$this->outputLine('%d commands match the command identifier "%s":', array($numberOfMatchingCommands, $commandIdentifier));
				$this->displayShortHelpForCommands($matchingCommands);
			} else {
				$this->displayHelpForCommand(array_shift($matchingCommands));
			}
		}
	}

	/**
	 * @return void
	 */
	protected function displayHelpIndex() {
		$context = $this->bootstrap->getContext();

		$this->outputLine('<b>TYPO3 Flow %s ("%s" context)</b>', array($this->packageManager->getPackage('TYPO3.Flow')->getPackageMetaData()->getVersion() ?: FLOW_VERSION_BRANCH, $context));
		$this->outputLine('<i>usage: %s <command identifier></i>', array($this->getFlowInvocationString()));
		$this->outputLine();
		$this->outputLine('The following commands are currently available:');

		$this->displayShortHelpForCommands($this->commandManager->getAvailableCommands());

		$this->outputLine('* = compile time command');
		$this->outputLine();
		$this->outputLine('See "%s help <commandidentifier>" for more information about a specific command.', array($this->getFlowInvocationString()));
		$this->outputLine();
	}

	/**
	 * @param array<\TYPO3\Flow\Cli\Command> $commands
	 * @return void
	 */
	protected function displayShortHelpForCommands(array $commands) {
		$commandsByPackagesAndControllers = $this->buildCommandsIndex($commands);
		foreach ($commandsByPackagesAndControllers as $packageKey => $commandControllers) {
			$this->outputLine('');
			$this->outputLine('PACKAGE "%s":', array(strtoupper($packageKey)));
			$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));
			foreach ($commandControllers as $commands) {
				foreach ($commands as $command) {
					$description = wordwrap($command->getShortDescription(), self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
					$shortCommandIdentifier = $this->commandManager->getShortestIdentifierForCommand($command);
					$compileTimeSymbol = ($this->bootstrap->isCompileTimeCommand($shortCommandIdentifier) ? '*' : '');
					$this->outputLine('%-2s%-40s %s', array($compileTimeSymbol, $shortCommandIdentifier , $description));
				}
				$this->outputLine();
			}
		}
	}

	/**
	 * Render help text for a single command
	 *
	 * @param \TYPO3\Flow\Cli\Command $command
	 * @return void
	 */
	protected function displayHelpForCommand(\TYPO3\Flow\Cli\Command $command) {
		$this->outputLine();
		$this->outputLine('<u>' . $command->getShortDescription() . '</u>');
		$this->outputLine();

		$this->outputLine('<b>COMMAND:</b>');
		$name = '<i>' . $command->getCommandIdentifier() . '</i>';
		$this->outputLine('%-2s%s', array(' ', $name));

		$commandArgumentDefinitions = $command->getArgumentDefinitions();
		$usage = '';
		$hasOptions = FALSE;
		foreach ($commandArgumentDefinitions as $commandArgumentDefinition) {
			if (!$commandArgumentDefinition->isRequired()) {
				$hasOptions = TRUE;
			} else {
				$usage .= sprintf(' <%s>', strtolower(preg_replace('/([A-Z])/', ' $1', $commandArgumentDefinition->getName())));
			}
		}

		$usage = $this->commandManager->getShortestIdentifierForCommand($command) . ($hasOptions ? ' [<options>]' : '') . $usage;

		$this->outputLine();
		$this->outputLine('<b>USAGE:</b>');
		$this->outputLine('  %s %s', array($this->getFlowInvocationString(), $usage));

		$argumentDescriptions = array();
		$optionDescriptions = array();

		if ($command->hasArguments()) {
			foreach ($commandArgumentDefinitions as $commandArgumentDefinition) {
				$argumentDescription = $commandArgumentDefinition->getDescription();
				$argumentDescription = wordwrap($argumentDescription, self::MAXIMUM_LINE_LENGTH - 23, PHP_EOL . str_repeat(' ', 23), TRUE);
				if ($commandArgumentDefinition->isRequired()) {
					$argumentDescriptions[] = vsprintf('  %-20s %s', array($commandArgumentDefinition->getDashedName(), $argumentDescription));
				} else {
					$optionDescriptions[] = vsprintf('  %-20s %s', array($commandArgumentDefinition->getDashedName(), $argumentDescription));
				}
			}
		}

		if (count($argumentDescriptions) > 0) {
			$this->outputLine();
			$this->outputLine('<b>ARGUMENTS:</b>');
			foreach ($argumentDescriptions as $argumentDescription) {
				$this->outputLine($argumentDescription);
			}
		}

		if (count($optionDescriptions) > 0) {
			$this->outputLine();
			$this->outputLine('<b>OPTIONS:</b>');
			foreach ($optionDescriptions as $optionDescription) {
				$this->outputLine($optionDescription);
			}
		}

		if ($command->getDescription() !== '') {
			$this->outputLine();
			$this->outputLine('<b>DESCRIPTION:</b>');
			$descriptionLines = explode(chr(10), $command->getDescription());
			foreach ($descriptionLines as $descriptionLine) {
				$this->outputLine('%-2s%s', array(' ', $descriptionLine));
			}
		}

		$relatedCommandIdentifiers = $command->getRelatedCommandIdentifiers();
		if ($relatedCommandIdentifiers !== array()) {
			$this->outputLine();
			$this->outputLine('<b>SEE ALSO:</b>');
			foreach ($relatedCommandIdentifiers as $commandIdentifier) {
				try {
					$command = $this->commandManager->getCommandByIdentifier($commandIdentifier);
					$this->outputLine('%-2s%s (%s)', array(' ', $commandIdentifier, $command->getShortDescription()));
				} catch (\TYPO3\Flow\Mvc\Exception\CommandException $exception) {
					$this->outputLine('%-2s%s (%s)', array(' ', $commandIdentifier, '<i>Command not available</i>'));
				}
			}
		}

		$this->outputLine();
	}

	/**
	 * Displays an error message
	 *
	 * @Flow\Internal
	 * @param \TYPO3\Flow\Mvc\Exception\CommandException $exception
	 * @return void
	 */
	public function errorCommand(\TYPO3\Flow\Mvc\Exception\CommandException $exception) {
		$this->outputLine($exception->getMessage());
		if ($exception instanceof \TYPO3\Flow\Mvc\Exception\AmbiguousCommandIdentifierException) {
			$this->outputLine('Please specify the complete command identifier. Matched commands:');
			$this->displayShortHelpForCommands($exception->getMatchingCommands());
		}
		$this->outputLine();
		$this->outputLine('Enter "%s help" for an overview of all available commands', array($this->getFlowInvocationString()));
		$this->outputLine('or "%s help <commandIdentifier>" for a detailed description of the corresponding command.', array($this->getFlowInvocationString()));
	}

	/**
	 * Builds an index of available commands. For each of them a Command object is
	 * added to the commands array of this class.
	 *
	 * @param array<\TYPO3\Flow\Cli\Command> $commands
	 * @return array in the format array('<packageKey>' => array('<CommandControllerClassName>', array('<command1>' => $command1, '<command2>' => $command2)))
	 */
	protected function buildCommandsIndex(array $commands) {
		$commandsByPackagesAndControllers = array();
		foreach ($commands as $command) {
			if ($command->isInternal()) {
				continue;
			}
			$commandIdentifier = $command->getCommandIdentifier();
			$packageKey = strstr($commandIdentifier, ':', TRUE);
			$commandControllerClassName = $command->getControllerClassName();
			$commandName = $command->getControllerCommandName();
			$commandsByPackagesAndControllers[$packageKey][$commandControllerClassName][$commandName] = $command;
		}
		return $commandsByPackagesAndControllers;
	}
}
namespace TYPO3\Flow\Command;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A Command Controller which provides help for available commands
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class HelpCommandController extends HelpCommandController_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'TYPO3\Flow\Command\HelpCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Command\HelpCommandController', $this);
		parent::__construct();
		if ('TYPO3\Flow\Command\HelpCommandController' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'TYPO3\Flow\Command\HelpCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Command\HelpCommandController', $this);

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
	$reflectedClass = new \ReflectionClass('TYPO3\Flow\Command\HelpCommandController');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Flow\Command\HelpCommandController', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Flow\Command\HelpCommandController', $propertyName, 'var');
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
		$this->injectPackageManager(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Package\PackageManagerInterface'));
		$this->injectBootstrap(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Core\Bootstrap'));
		$this->injectCommandManager(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Cli\CommandManager'));
		$this->injectReflectionService(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService'));
$this->Flow_Injected_Properties = array (
  0 => 'packageManager',
  1 => 'bootstrap',
  2 => 'commandManager',
  3 => 'reflectionService',
);
	}
}
#