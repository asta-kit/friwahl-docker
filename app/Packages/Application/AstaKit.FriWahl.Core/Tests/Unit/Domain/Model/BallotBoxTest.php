<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */
use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Election;


/**
 * Testcase for Ballot box
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class BallotBoxTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @return Election
	 */
	protected function getMockedElection() {
		return $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Election', array(), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function canBeEmittedWhenNew() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$this->assertEquals(BallotBox::STATUS_NEW, $box->getStatus());

		$box->emit();
		$this->assertEquals(BallotBox::STATUS_EMITTED, $box->getStatus());
	}

	/**
	 * @test
	 */
	public function tellsItIsReadyToBeEmittedWhenNew() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$this->assertTrue($box->isReadyToBeEmitted());
	}

	/**
	 * @test
	 */
	public function isNotAvailableForVotingSessionWhenNew() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$this->assertFalse($box->isAvailableForVotingSession());
	}

	/**
	 * @test
	 */
	public function isAvailableForVotingSessionWhenEmitted() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$box->emit();
		$this->assertTrue($box->isAvailableForVotingSession());
	}

	/**
	 * @test
	 */
	public function cannotBeEmittedAgainWhenEmitted() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$box->emit();
		$this->assertFalse($box->isReadyToBeEmitted());
	}

	/**
	 * @test
	 */
	public function isNotAvailableForVotingSessionWhenReturned() {
		$box = new BallotBox(uniqid(), uniqid(), $this->getMockedElection());

		$box->emit();
		$box->returnBox();

		$this->assertFalse($box->isAvailableForVotingSession());
	}

}