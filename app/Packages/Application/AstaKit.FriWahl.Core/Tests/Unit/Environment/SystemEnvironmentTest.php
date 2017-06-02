<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Environment;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Environment\SystemEnvironment;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the system environment class.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class SystemEnvironmentTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function getCurrentDateReturnsCurrentDate() {
		$systemEnvironment = new SystemEnvironment();

		$this->assertEquals(new \DateTime(), $systemEnvironment->getCurrentDate());
	}

	/**
	 * @test
	 */
	public function getCurrentDateReturnsCurrentDateAfterFewSeconds() {
		$systemEnvironment = new SystemEnvironment();

		$this->assertEquals(new \DateTime(), $systemEnvironment->getCurrentDate());
		sleep(2);
		$this->assertEquals(new \DateTime(), $systemEnvironment->getCurrentDate());
	}

	/**
	 * @test
	 */
	public function getCurrentDateReturnsSetDateIfDateWasSet() {
		$systemEnvironment = new SystemEnvironment();
		$mockDate = new \DateTime('+10 seconds');
		$this->inject($systemEnvironment, 'mockedDate', $mockDate);

		$this->assertEquals($mockDate, $systemEnvironment->getCurrentDate());
	}
}
 