<?php
namespace AstaKit\FriWahl\VoterDirectory\Tests\Unit\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */

use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFileFormat;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\ImportFileLineParser;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 *
 *
 * TODO use mocked import file format objects instead of real ones
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportFileLineParserTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function fieldsDefinedInConfigurationAreCorrectlyMapped() {
		$formatConfiguration = array(
			'fieldSeparator' => ',',
			'fieldWrap' => '"',
			'fields' => array(
				array(
					'type' => 'property',
					'name' => 'familyName',
				),
				array(
					'type' => 'property',
					'name' => 'anotherField',
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
"the name", "another field's contents"
DOC;
		$parsingResult = $parser->parseLine($lineToParse);

		$this->assertEquals('the name', $parsingResult['properties']['familyName']);
		$this->assertEquals('another field\'s contents', $parsingResult['properties']['anotherField']);
	}

	/**
	 * @test
	 */
	public function discriminatorsDefinedInConfigurationAreCorrectlyMapped() {
		$formatConfiguration = array(
			'fieldSeparator' => ',',
			'fieldWrap' => '"',
			'fields' => array(
				array(
					'skip' => TRUE,
				),
				array(
					'type' => 'discriminator',
					'name' => 'discriminatorA',
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
"the name", "some discriminator value"
DOC;
		$parsingResult = $parser->parseLine($lineToParse);

		$this->assertEquals('some discriminator value', $parsingResult['discriminators']['discriminatorA']);
	}

	/**
	 * @test
	 */
	public function definedFieldSeparatorAndWrapAreRespected() {
		$formatConfiguration = array(
			'fieldSeparator' => ';',
			'fieldWrap' => "'", // use ' instead of " as field wrap
			'fields' => array(
				array(
					'type' => 'property',
					'name' => 'familyName',
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
'the name'; 'more contents'
DOC;
		$parsingResult = $parser->parseLine($lineToParse);

		$this->assertEquals('the name', $parsingResult['properties']['familyName']);
	}

	/**
	 * @test
	 */
	public function fieldValueIsTrimmedIfConfigured() {
		$formatConfiguration = array(
			'fieldSeparator' => ';',
			'fieldWrap' => "'", // use ' instead of " as field wrap
			'fields' => array(
				array(
					'type' => 'property',
					'name' => 'familyName',
					'preProcessing' => array(
						'trim',
					)
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
'   the name with spaces around  '
DOC;
		$parsingResult = $parser->parseLine($lineToParse);

		$this->assertEquals('the name with spaces around', $parsingResult['properties']['familyName']);
	}

	/**
	 * @test
	 */
	public function discriminatorValueIsMappedWithValueMapIfConfigured() {
		$formatConfiguration = array(
			'fieldSeparator' => ';',
			'fieldWrap' => "'", // use ' instead of " as field wrap
			'fields' => array(
				array(
					'type' => 'discriminator',
					'name' => 'someDiscriminator',
					'valueMap' => array(
						'foo' => 'bar',
					)
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
'foo'
DOC;
		$parsingResult = $parser->parseLine($lineToParse);

		$this->assertEquals('bar', $parsingResult['discriminators']['someDiscriminator']);
	}

	/**
	 * @test
	 */
	public function mappingFailsIfValueInValueMapIsNotSet() {
		$this->setExpectedException('UnexpectedValueException', '', 1401456122);

		$formatConfiguration = array(
			'fieldSeparator' => ';',
			'fieldWrap' => "'", // use ' instead of " as field wrap
			'fields' => array(
				array(
					'type' => 'discriminator',
					'name' => 'someDiscriminator',
					'failIfMissing' => TRUE,
					'valueMap' => array(
						// empty value map so every value will fail
					)
				),
			),
		);
		$formatConfiguration = ImportFileFormat::createFromConfiguration('', $formatConfiguration);

		$parser = new ImportFileLineParser($formatConfiguration);

		$lineToParse = <<<DOC
'foo'
DOC;
		$parser->parseLine($lineToParse);
	}
}
