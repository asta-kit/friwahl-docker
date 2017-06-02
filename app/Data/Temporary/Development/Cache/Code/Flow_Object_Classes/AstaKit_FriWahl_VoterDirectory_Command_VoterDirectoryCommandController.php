<?php 
namespace AstaKit\FriWahl\VoterDirectory\Command;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFile;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFileFormat;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportTask;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Result;
use TYPO3\Flow\Error;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;


/**
 * @Flow\Scope("singleton")
 */
class VoterDirectoryCommandController_Original extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * The current indentation level
	 * @var int
	 */
	protected $currentIndent = 0;

	/**
	 * Outputs the given error message and closes the program.
	 *
	 * @param string $errorMessage
	 * @param array $parameters
	 */
	protected function quitWithErrorMessage($errorMessage, array $parameters = array()) {
		$this->outputLine($errorMessage, $parameters);
		$this->quit(1);
	}

	/**
	 * Lists the available import formats
	 *
	 * @return void
	 */
	public function listFormatsCommand() {
		$this->outputLine('Die folgenden Formate sind zum Import verfügbar:');
		$this->outputLine('');

		foreach ($this->settings['importFormats'] as $name => $format) {
			$this->outputLine('Name: %s', array($name));

			$this->indent();

			$this->outputLine('Definierte Felder:');

			$this->indent();
			foreach ($format['fields'] as $key => $field) {
				$this->describeField($key, $field);
			}

			$this->outdent();
		}
	}

	/**
	 * Describes the given field.
	 *
	 * @param int $identifier The field number
	 * @param array $fieldConfiguration
	 */
	protected function describeField($identifier, $fieldConfiguration) {
		$this->outputLine('');
		$this->outputLine('Feld Nr. %u', array($identifier));
		$this->indent();

		if (isset($fieldConfiguration['skip']) && $fieldConfiguration['skip'] === TRUE) {
			$this->outputLine('übersprungen', array($identifier));
			$this->outdent();
			return;
		}

		switch ($fieldConfiguration['type']) {
			case 'discriminator':
				$this->outputLine('Typ: Diskriminator');
				$this->outputLine('Diskriminator: %s', array($fieldConfiguration['name']));

				if (isset($fieldConfiguration['valueMap'])) {
					$this->outputLine();
					$this->outputLine('Werte-Zuordnung:');
					$this->indent();
					foreach ($fieldConfiguration['valueMap'] as $source => $target) {
						$this->outputLine('- %s => %s', array($source, $target));
					}
					$this->outdent();
				}

				break;

			case 'field':
				$this->outputLine('Typ: Feld');
				$this->outputLine('Feld: %s', array($fieldConfiguration['name']));

				break;

			default:
				$this->outputLine('unbekannter Typ');
		}

		if (isset($fieldConfiguration['preProcessing'])) {
			$this->outputLineStart('Vorverarbeitung: ');
			if (in_array('trim', $fieldConfiguration['preProcessing'])) {
				$this->output('trim');
			}
			$this->output("\n");
		}
		$this->outdent();
	}

	/**
	 * Imports a list of voters from a file.
	 *
	 * The format of the imported file can be freely defined, see Configuration/Settings.yaml and describeField() in
	 * this class for more information.
	 *
	 * @param Election $election The election to add the voters for
	 * @param string $filePath The file to import.
	 * @param string $format The format used to parse this file
	 * @param bool $simulate The
	 * @return void
	 */
	public function importFileCommand($election, $filePath, $format, $simulate = FALSE) {
		if (!is_file($filePath)) {
			$this->quitWithErrorMessage("File '%s' does not exist or is no file.", array($filePath));
		}
		if (!isset($this->settings['importFormats'][$format])) {
			$this->quitWithErrorMessage("Format %s is not defined", array($format));
		}
		$formatDefinition = $this->settings['importFormats'][$format];
		$inputFileFormat = ImportFileFormat::createFromConfiguration($format, $formatDefinition);
		$importFile = new ImportFile($filePath, $inputFileFormat);
		$importTask = new ImportTask($election, $importFile);

		$this->outputLine('Datei %s wurde geöffnet. %u Einträge', array($filePath, $importFile->getLineCount()));

		if ($simulate) {
			$importTask->enableSimulation();
			$this->outputLine('=============================================');
			$this->outputLine('Simuliere Import');
			$this->outputLine('=============================================');
			$this->outputLine();
		}

		$results = $importTask->execute();
		$this->outputLine('%u Wähler importiert', array($results->getImportedRecordsCount()));

		// TODO move this to a result printer
		/** @var $subResults Result */
		foreach ($results->getSubResults() as $key => $subResults) {
			$this->outputLine("Fehler für Eintrag $key ");
			/** @var $warning Error\Warning */
			foreach ($subResults->getWarnings() as $warning) {
				$this->outputLine("  [W] " . $warning->getMessage());
			}
			/** @var $error Error\Error */
			foreach ($subResults->getErrors() as $error) {
				$this->outputLine("  [E] " . $error->getMessage());
			}
		}

		$this->persistenceManager->persistAll();

		$this->outputLine("Maximale Speichernutzung: %s", array(memory_get_peak_usage(TRUE)));
	}

	/**
	 * Outputs a line with the correct indentation
	 *
	 * @param string $text
	 * @param array $arguments
	 */
	protected function outputLine($text = '', array $arguments = array()) {
		$padding = str_pad('', $this->currentIndent, ' ');
		parent::outputLine($padding . $text, $arguments);
	}

	/**
	 * Starts a line with correct indentation, but does not print a line ending character (like output())
	 *
	 * @param string $text
	 * @param array $arguments
	 */
	protected function outputLineStart($text = '', array $arguments = array()) {
		$padding = str_pad('', $this->currentIndent, ' ');
		$this->output($padding . $text, $arguments);
	}

	/**
	 * Increases indentation by the given level
	 *
	 * @param int $levels
	 */
	protected function indent($levels = 1) {
		$this->currentIndent += 2 * $levels;
	}

	/**
	 * Decreases indentation by the given level
	 *
	 * @param int $levels
	 */
	protected function outdent($levels = 1) {
		$this->currentIndent -= 2 * $levels;
		if ($this->currentIndent < 0) {
			$this->currentIndent = 0;
		}
	}

}namespace AstaKit\FriWahl\VoterDirectory\Command;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * 
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class VoterDirectoryCommandController extends VoterDirectoryCommandController_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController', $this);
		parent::__construct();
		if ('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController', $this);

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
	$reflectedClass = new \ReflectionClass('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('AstaKit\FriWahl\VoterDirectory\Command\VoterDirectoryCommandController', $propertyName, 'var');
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
		$this->injectSettings(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Configuration\ConfigurationManager')->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'AstaKit.FriWahl.VoterDirectory'));
		$this->injectReflectionService(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService'));
		$persistenceManager_reference = &$this->persistenceManager;
		$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		if ($this->persistenceManager === NULL) {
			$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('f1bc82ad47156d95485678e33f27c110', $persistenceManager_reference);
			if ($this->persistenceManager === NULL) {
				$this->persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('f1bc82ad47156d95485678e33f27c110',  $persistenceManager_reference, 'TYPO3\Flow\Persistence\Doctrine\PersistenceManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'reflectionService',
  2 => 'persistenceManager',
);
	}
}
#