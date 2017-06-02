<?php
namespace AstaKit\FriWahl\Core\Tests\Functional\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\VotingTurnout;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the turnout class.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class TurnoutTest extends UnitTestCase {

	protected function getMockedVoting() {
		return $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function totalVoterAndVoteCountIsCorrectlyExposed() {
		$turnout = new VotingTurnout($this->getMockedVoting(), array(
			'_all' => array('votes' => 10, 'voters' => 100)
		));

		$this->assertEquals(10, $turnout->getTotalVotes());
		$this->assertEquals(100, $turnout->getTotalVoters());
	}

	/**
	 * @test
	 */
	public function votingTurnoutIsCorrectlyCalculated() {
		$turnout = new VotingTurnout($this->getMockedVoting(), array(
			'_all' => array('votes' => 10, 'voters' => 100)
		));

		$this->assertEquals(0.1, $turnout->getTotalTurnout());
	}

}
 