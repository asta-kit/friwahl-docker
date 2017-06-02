<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */
use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\ElectionPeriod;

/**
 * Testcase for ElectionPeriod
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ElectionPeriodTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function constructorThrowsExceptionIfStartDateIsAfterEndDate() {
		$this->setExpectedException('InvalidArgumentException');

		new ElectionPeriod(new \DateTime('+10 seconds'), new \DateTime('-10 seconds'), new Election('', ''));
	}

	/**
	 * @test
	 */
	public function newPeriodIsAddedToElectionByConstructor() {
		$mockedElection = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Election', array(), array(), '', FALSE);
		$mockedElection->expects($this->once())->method('addPeriod')->with($this->isInstanceOf('AstaKit\FriWahl\Core\Domain\Model\ElectionPeriod'));
		/** @var Election $mockedElection */

		new ElectionPeriod(new \DateTime('-10 seconds'), new \DateTime('+10 seconds'), $mockedElection);
	}

	/**
	 * Data provider for points in time which should be included in the given period
	 */
	public function includedPointsInTimeDataProvider() {
		return array(
			// start date, end date, date to test
			'startDate' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-29T12:30:10'
			),
			'one second after start date' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-29T12:30:11'
			),
			'date to test with different timezone' => array(
				// the trick here is that without the timezone, the date to test would *not* be within the period
				'2014-04-29T12:30:10+00:00', '2014-04-30T20:30:10+00:00', '2014-04-29T11:30:11-01:00'
			),
			'end date' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-29T20:30:10'
			),
			'one second before end date' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-30T20:30:09'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider includedPointsInTimeDataProvider
	 */
	public function includesReturnsTrueIfGivenPointInTimeIsInPeriod($startDate, $endDate, $dateToTest) {
		$period = new ElectionPeriod(new \DateTime($startDate), new \DateTime($endDate), new Election('', ''));

		$this->assertTrue($period->includes(new \DateTime($dateToTest)));
	}

	/**
	 * Data provider for points in time which should be included in the given period
	 */
	public function notIncludedPointsInTimeDataProvider() {
		return array(
			// start date, end date, date to test
			'one second before start date' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-29T12:30:09'
			),
			'1970-01-01' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '1970-01-01T00:00:00'
			),
			'date to test with different timezone' => array(
				// the trick here is that without the timezone, the date to test would be within the period
				'2014-04-29T12:30:10+00:00', '2014-04-30T20:30:10+00:00', '2014-04-29T13:30:09+01:00'
			),
			'end of 21st century' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2099-12-31T23:59:59'
			),
			'one second after end date' => array(
				'2014-04-29T12:30:10', '2014-04-30T20:30:10', '2014-04-30T20:30:11'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider notIncludedPointsInTimeDataProvider
	 */
	public function includesReturnsFalseIfGivenPointInTimeIsNotInPeriod($startDate, $endDate, $dateToTest) {
		$period = new ElectionPeriod(new \DateTime($startDate), new \DateTime($endDate), new Election('', ''));

		$this->assertFalse($period->includes(new \DateTime($dateToTest)));
	}
}