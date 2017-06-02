<?php
namespace AstaKit\FriWahl\VoterDirectory\Tests\Unit\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */

use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFile;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFileFormat;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the import file abstraction
 *
 * TODO test if all lines are read and correct line count is returned if file does not end with newline character
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportFileTest extends UnitTestCase {

	protected $fileConfigurations = array(
		'SimpleImportFile.txt' => array(
			'fieldSeparator' => ',',
			'fieldWrap' => '"',
			'fields' => array(
				array(
					'type' => 'property',
					'name' => 'firstField',
				),
				array(
					'type' => 'property',
					'name' => 'secondField',
				),
				array(
					'type' => 'property',
					'name' => 'thirdField',
				),
			),
		),
	);

	/**
	 * @param $fileName
	 */
	protected function createFixture($fileName) {
		$formatDescription = $this->fileConfigurations[$fileName];
		$formatDescription = ImportFileFormat::createFromConfiguration('', $formatDescription);

		return new ImportFile(__DIR__ . '/Fixtures/' . $fileName, $formatDescription);
	}

	/**
	 * @test
	 */
	public function currentReturnsArrayWithFieldInformation() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$parsedCurrentLine = $fixture->current();

		$this->assertEquals(array(
			'firstField' => "1",
			'secondField' => "Some simple file",
			'thirdField' => "with three fields per line",
		), $parsedCurrentLine['properties']);
	}

	/**
	 * @test
	 */
	public function nextAfterRewindMovesToSecondLine() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$fixture->next();
		$fixture->rewind();
		// at first line again
		$fixture->next();
		$parsedCurrentLine = $fixture->current();

		$this->assertEquals("2", $parsedCurrentLine['properties']['firstField']);
	}

	/**
	 * @test
	 */
	public function getLineCountReturnsCorrectLineCount() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$this->assertEquals(2, $fixture->getLineCount());
	}

	/**
	 * @test
	 */
	public function getLineCountReturnsCorrectLineCountAfterAFewLinesHaveBeenRead() {
		$fixture = $this->createFixture('SimpleImportFile.txt');
		$fixture->next();
		$fixture->next();

		$this->assertEquals(2, $fixture->getLineCount());
	}

	/**
	 * @test
	 */
	public function getLineCountDoesNotChangeCurrentPositionInFile() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$valueBeforeLineCount = $fixture->current();
		$fixture->getLineCount();
		$valueAfterLineCount = $fixture->current();

		$this->assertEquals($valueBeforeLineCount, $valueAfterLineCount);
	}

	/**
	 * @test
	 */
	public function nextMovesToNextLine() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$fixture->next();
		$parsedCurrentLine = $fixture->current();

		$this->assertEquals(array(
			'firstField' => "2",
			'secondField' => "another line",
			'thirdField' => "with a third field",
		), $parsedCurrentLine['properties']);
	}

	/**
	 * @test
	 */
	public function keyReturnsCurrentLineNumber() {
		$fixture = $this->createFixture('SimpleImportFile.txt');

		$this->assertEquals(1, $fixture->key());
		$fixture->next();

		$this->assertEquals(2, $fixture->key());
	}
}
