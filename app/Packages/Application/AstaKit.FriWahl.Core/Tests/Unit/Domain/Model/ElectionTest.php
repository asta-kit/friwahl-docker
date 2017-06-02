<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\ElectionPeriod;
use AstaKit\FriWahl\Core\Environment\SystemEnvironment;


/**
 * Testcase for Election
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ElectionTest extends \TYPO3\Flow\Tests\UnitTestCase {

	protected $currentDate;

	/**
	 * @return Election
	 */
	protected function createElection() {
		$election = new Election('', '');
		$this->inject($election, 'systemEnvironment', $this->createMockedSystemEnvironment());

		return $election;
	}

	/**
	 * @return SystemEnvironment
	 */
	protected function createMockedSystemEnvironment() {
		$systemEnvironment = $this->getMock('AstaKit\FriWahl\Core\Environment\SystemEnvironment');
		if (!$this->currentDate) {
			$this->currentDate = new \DateTime();
		}
		$systemEnvironment->expects($this->any())->method('getCurrentDate')->will($this->returnValue($this->currentDate));

		return $systemEnvironment;
	}

	/**
	 * @test
	 */
	public function isActiveReturnsFalseIfNoPeriodIsConfigured() {
		$election = $this->createElection();

		$this->assertFalse($election->isActive());
	}

	/**
	 * @test
	 */
	public function isActiveReturnsTrueIfCurrentTimeIsWithinTheOnlyConfiguredPeriod() {
		$election = $this->createElection();
		new ElectionPeriod(new \DateTime('-10 seconds'), new \DateTime('+10 seconds'), $election);

		$this->assertTrue($election->isActive());
	}

	/**
	 * @test
	 */
	public function isActiveReturnsTrueIfCurrentTimeIsInTheSecondConfiguredPeriods() {
		$this->currentDate = new \DateTime('+25 seconds');

		$election = $this->createElection();
		new ElectionPeriod(new \DateTime('-10 seconds'), new \DateTime('+10 seconds'), $election);
		new ElectionPeriod(new \DateTime('+20 seconds'), new \DateTime('+30 seconds'), $election);

		$this->assertTrue($election->isActive());
	}

	/**
	 * @test
	 */
	public function isActiveReturnsFalseIfCurrentTimeIsBetweenTwoConfiguredPeriods() {
		$this->currentDate = new \DateTime('+15 seconds');

		$election = $this->createElection();
		new ElectionPeriod(new \DateTime('-10 seconds'), new \DateTime('+10 seconds'), $election);
		new ElectionPeriod(new \DateTime('+20 seconds'), new \DateTime('+30 seconds'), $election);

		$this->assertFalse($election->isActive());
	}
}