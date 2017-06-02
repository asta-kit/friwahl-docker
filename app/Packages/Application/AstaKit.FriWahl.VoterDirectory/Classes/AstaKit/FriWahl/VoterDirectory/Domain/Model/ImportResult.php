<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;
use TYPO3\Flow\Error\Result;


/**
 * Object for storing the result of an import session.
 *
 * TODO check if this still behaves well with lots of objects (~ 25.000), as we currently only inherit from Flow's error
 * handling classes
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportResult extends Result {

	/**
	 * The number of imported records
	 *
	 * @var int
	 */
	protected $importedRecords = 0;

	/**
	 * @return void
	 */
	public function addImportedRecord() {
		++$this->importedRecords;
	}

	/**
	 * Returns the number of imported records
	 *
	 * @return int
	 */
	public function getImportedRecordsCount() {
		return $this->importedRecords;
	}
}
