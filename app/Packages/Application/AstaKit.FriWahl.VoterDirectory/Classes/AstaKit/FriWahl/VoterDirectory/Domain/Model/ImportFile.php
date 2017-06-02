<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */

use TYPO3\Flow\Annotations as Flow;


/**
 * Model for a file voters should be imported from.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * TODO check if this should implement ArrayAccess/Iterator/IteratorAggregate
 */
class ImportFile implements \Iterator {

	/**
	 * The path of the file to import
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * The contents of the current line
	 *
	 * @var string
	 */
	protected $currentLine;

	/**
	 * @var int
	 */
	protected $currentLineNumber = 0;

	/**
	 * File handle for the import file
	 *
	 * @var resource
	 */
	protected $fileHandle;

	/**
	 * The line parser used for this file
	 *
	 * @var ImportFileLineParser
	 */
	protected $lineParser;

	/**
	 * The number of lines in the file. See getLineCount()
	 *
	 * @var integer
	 */
	protected $numberOfLines;

	protected $persistenceManager;

	/**
	 * @param string $file
	 * @param ImportFileFormat $formatDescription
	 */
	public function __construct($file, $formatDescription) {
		$this->file = $file;
		$this->fileHandle = fopen($this->file, 'r');

		$this->lineParser = new ImportFileLineParser($formatDescription);

		// move to first line
		$this->rewind();
	}

	public function importCurrentLine() {
		// TODO implement
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		if ($this->currentLine === NULL) {
			// we're at the start of the file, so read the first line
			$this->next();
		}
		return $this->lineParser->parseLine($this->currentLine);
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		++$this->currentLineNumber;
		$this->currentLine = fgets($this->fileHandle);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->currentLineNumber;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return !feof($this->fileHandle);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		rewind($this->fileHandle);
		$this->currentLine = NULL;
		$this->currentLineNumber = 0;

		// move to first line
		$this->next();
	}

	/**
	 * Returns the number of lines in the file. The number is cached internally.
	 *
	 * @return integer
	 */
	public function getLineCount() {
		if ($this->numberOfLines === NULL) {
			$storedPosition = ftell($this->fileHandle);

			rewind($this->fileHandle);
			$this->numberOfLines = 0;
			while (!feof($this->fileHandle)) {
				$this->numberOfLines += substr_count(fread($this->fileHandle, 8192), "\n");
			}

			fseek($this->fileHandle, $storedPosition);
		}

		return $this->numberOfLines;
	}
}
 