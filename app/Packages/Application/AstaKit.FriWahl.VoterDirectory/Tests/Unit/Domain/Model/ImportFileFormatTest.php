<?php
namespace AstaKit\FriWahl\VoterDirectory\Tests\Unit\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */

use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFileFormat;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test for the import file format description.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportFileFormatTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function objectCreationFailsIfFieldSeparatorIsNotDefined() {
		$this->setExpectedException('InvalidArgumentException', '', 1401554952);

		$formatDefinition = array(
			'fieldWrap' => '"',
			'fields' => array(),
		);

		ImportFileFormat::createFromConfiguration('', $formatDefinition);
	}

	/**
	 * @test
	 */
	public function objectCreationFailsIfFieldWrapIsNotDefined() {
		$this->setExpectedException('InvalidArgumentException', '', 1401554952);

		$formatDefinition = array(
			'fieldSeparator' => ',',
			'fields' => array(),
		);

		ImportFileFormat::createFromConfiguration('', $formatDefinition);
	}

	/**
	 * @test
	 */
	public function objectCreationFailsIfFieldSeparatorIsTwoCharactersLong() {
		$this->setExpectedException('InvalidArgumentException', '', 1401554953);

		$formatDefinition = array(
			'fieldWrap' => '"',
			'fieldSeparator' => '--',
			'fields' => array(),
		);

		ImportFileFormat::createFromConfiguration('', $formatDefinition);
	}

	/**
	 * @test
	 */
	public function objectCreationFailsIfFieldWrapIsTwoCharactersLong() {
		$this->setExpectedException('InvalidArgumentException', '', 1401554954);

		$formatDefinition = array(
			'fieldWrap' => '"!',
			'fieldSeparator' => ',',
			'fields' => array(),
		);

		ImportFileFormat::createFromConfiguration('', $formatDefinition);
	}
}
 