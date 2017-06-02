<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;


use AstaKit\FriWahl\Core\Domain\Model as CoreModel;
use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;


/**
 * An import task performed by the system.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportTask {

	/**
	 * @var ImportResult
	 */
	protected $importResults;

	/**
	 * The file to import
	 *
	 * @var ImportFile
	 */
	protected $sourceFile;

	/**
	 * Toggle for the simulation mode. If this is set, the import is only simulated, i.e. the file is checked
	 *
	 * @var bool
	 */
	protected $simulate = FALSE;

	/**
	 * The election to import voters to
	 * @var CoreModel\Election
	 */
	protected $election;

	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;


	public function __construct($election, ImportFile $sourceFile) {
		$this->election = $election;
		$this->sourceFile = $sourceFile;
		$this->importResults = new ImportResult();
	}

	/**
	 * @return void
	 */
	public function enableSimulation() {
		$this->simulate = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableSimulation() {
		$this->simulate = FALSE;
	}

	/**
	 * Performs the import task.
	 *
	 * @return ImportResult
	 */
	public function execute() {
		foreach ($this->sourceFile as $key => $voterInformation) {
			$givenName = $voterInformation['properties']['givenName'];
			$familyName = $voterInformation['properties']['familyName'];

			if (!$givenName || !$familyName) {
				$this->importResults->forProperty($key)->addError(new Error\Error('Ungültiger Name'));
				continue;
			}

			// TODO check if voter with same name exists in database, add warning if yes

			$voter = new CoreModel\EligibleVoter($this->election, $givenName, $familyName);

			foreach ($voterInformation['discriminators'] as $name => $value) {
				$voter->addDiscriminator($name, $value);
				// TODO check discriminator for uniqueness
			}

			if (!$this->simulate) {
				$this->persistenceManager->add($voter);
			}

			$this->importResults->addImportedRecord();
		}

		return $this->importResults;
	}

	/**
	 * Returns the results of the import run.
	 *
	 * @return ImportResult
	 */
	public function getResults() {
		return $this->importResults;
	}
}
