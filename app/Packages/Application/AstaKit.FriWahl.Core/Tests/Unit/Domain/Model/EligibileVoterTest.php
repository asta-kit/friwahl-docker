<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the eligible voters class
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class EligibileVoterTest extends UnitTestCase {

	/**
	 * @return Election
	 */
	protected function getMockedElection() {
		return $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Election', array(), array(), '', FALSE);
	}

	/**
	 * @return EligibleVoter
	 */
	protected function createVoterFixture() {
		return new EligibleVoter($this->getMockedElection(), 'John', 'Doe');
	}

	/**
	 * @return EligibleVoter
	 */
	protected function createVoterWithNameAndMatriculationNumber($givenName, $familyName, $matriculationNumber) {
		$voter = new EligibleVoter($this->getMockedElection(), $givenName, $familyName);
		$voter->addDiscriminator('matriculationNumber', $matriculationNumber);

		return $voter;
	}

	/**
	 * @test
	 */
	public function addDiscriminatorAddsDiscriminatorToList() {
		$voter = $this->createVoterFixture();
		$voter->addDiscriminator('foo', 'bar');

		$this->assertCount(1, $voter->getDiscriminators());
	}

	/**
	 * @test
	 */
	public function discriminatorCanBeFetchedByIdentifier() {
		$voter = $this->createVoterFixture();
		$voter->addDiscriminator('foo', 'bar');

		$this->assertNotNull($voter->getDiscriminator('foo'));
	}

	/**
	 * @test
	 */
	public function hasDiscriminatorReturnsFalseIfNoDiscriminatorHasBeenAdded() {
		$voter = $this->createVoterFixture();

		$this->assertFalse($voter->hasDiscriminator('foo'));
	}

	/**
	 * @test
	 */
	public function hasDiscriminatorReturnsTrueAfterDiscriminatorHasBeenAdded() {
		$voter = $this->createVoterFixture();
		$voter->addDiscriminator('foo', 'bar');

		$this->assertTrue($voter->hasDiscriminator('foo'));
	}

	/**
	 * @test
	 */
	public function hasDiscriminatorReturnsFalseForOtherIdentifiersAfterDiscriminatorHasBeenAdded() {
		$voter = $this->createVoterFixture();
		$voter->addDiscriminator('foo', 'bar');

		$this->assertFalse($voter->hasDiscriminator('baz'));
	}

	/**
	 * @test
	 */
	public function identifierContainsMatriculationNumberAndCorrectLettersFromName() {
		$voter = $this->createVoterWithNameAndMatriculationNumber('Abc', 'Xyz', 12345);

		$this->assertEquals('12345AZ', $voter->getIdentifier());
	}

	public function umlautDataProvider() {
		return array(
			array("Ä", 'A'),
			array('ä', 'A'),
			array('Á', 'A'),
			array('á', 'A'),
			array('À', 'A'),
			array('à', 'A'),
			array('Ö', 'O'),
			array('ö', 'O'),
			array('Ó', 'O'),
			array('ó', 'O'),
			array('Ò', 'O'),
			array('ò', 'O'),
			array('Ü', 'U'),
			array('ü', 'U'),
			array('ß', 'S'),
			array('Ç', 'C'),
			array('ç', 'C'),
			array('É', 'E'),
			array('é', 'E'),
			array('È', 'E'),
			array('è', 'E'),
			array('Æ', 'A'),
			array('æ', 'A'),
			array('Ø', 'O'),
			array('ø', 'O'),
		);
	}

	/**
	 * @param string $umlaut
	 * @param string $normalizedUmlaut
	 *
	 * @test
	 * @dataProvider umlautDataProvider
	 */
	public function identifierContainsCorrectNormalizedFormOfUmlautInFirstName($umlaut, $normalizedUmlaut) {
		$voter = $this->createVoterWithNameAndMatriculationNumber($umlaut . 'bc', 'Xyz', 12345);

		$this->assertEquals('12345' . $normalizedUmlaut . 'Z', $voter->getIdentifier());
	}

	/**
	 * @param string $umlaut
	 * @param string $normalizedUmlaut
	 *
	 * @test
	 * @dataProvider umlautDataProvider
	 */
	public function identifierContainsCorrectNormalizedFormOfUmlautInLastName($umlaut, $normalizedUmlaut) {
		$voter = $this->createVoterWithNameAndMatriculationNumber('Abc', 'Xy' . $umlaut, 12345);

		$this->assertEquals('12345A' . $normalizedUmlaut, $voter->getIdentifier());
	}

}
