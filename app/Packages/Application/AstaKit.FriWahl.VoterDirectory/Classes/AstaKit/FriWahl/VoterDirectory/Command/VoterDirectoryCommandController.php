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
class VoterDirectoryCommandController extends \TYPO3\Flow\Cli\CommandController {

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

}