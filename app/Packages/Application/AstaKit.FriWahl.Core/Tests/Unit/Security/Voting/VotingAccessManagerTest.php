<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Security\Voting\VotingAccessManager;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the voting access manager
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingAccessManagerTest extends UnitTestCase {

	/**
	 * @return EligibleVoter
	 */
	protected function getMockedVoter() {
		return $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
	}

	/**
	 * @return Voting
	 */
	protected function getMockedVoting() {
		return $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function mayParticipateReturnsFalseIfNoVoterIsDefined() {
		$accessManager = new VotingAccessManager();
		$accessManager->setAccessVoters(array());
		$this->inject($accessManager, 'log', $this->getMock('TYPO3\Flow\Log\SystemLoggerInterface'));

		$this->assertFalse($accessManager->mayParticipate($this->getMockedVoter(), $this->getMockedVoting()));
	}

	/**
	 * @test
	 */
	public function mayParticipateDoesNotAskVoterForVoteIfItCannotVote() {
		$mockedAccessVoter = $this->getMockForAbstractClass('AstaKit\FriWahl\Core\Security\Voting\BaseVotingAccessVoter', array(), '', FALSE);
		$mockedAccessVoter->expects($this->atLeastOnce())->method('canVote')->will($this->returnValue(FALSE));
		$mockedAccessVoter->expects($this->never())->method('mayParticipate');

		$accessManager = new VotingAccessManager();
		$accessManager->setAccessVoters(array($mockedAccessVoter));
		$this->inject($accessManager, 'log', $this->getMock('TYPO3\Flow\Log\SystemLoggerInterface'));

		$accessManager->mayParticipate($this->getMockedVoter(), $this->getMockedVoting());
	}

	/**
	 * @test
	 */
	public function mayParticipateReturnsTrueIfVoterReturnsTrue() {
		$mockedAccessVoter = $this->getMockForAbstractClass('AstaKit\FriWahl\Core\Security\Voting\BaseVotingAccessVoter', array(), '', FALSE);
		$mockedAccessVoter->expects($this->atLeastOnce())->method('mayParticipate')->will($this->returnValue(TRUE));
		$mockedAccessVoter->expects($this->atLeastOnce())->method('canVote')->will($this->returnValue(TRUE));

		$accessManager = new VotingAccessManager();
		$accessManager->setAccessVoters(array($mockedAccessVoter));
		$this->inject($accessManager, 'log', $this->getMock('TYPO3\Flow\Log\SystemLoggerInterface'));

		$this->assertTrue($accessManager->mayParticipate($this->getMockedVoter(), $this->getMockedVoting()));
	}

	/**
	 * Test if FALSE is returned even if other voters called after the failing voter return TRUE again; this is a very
	 * fundamental test to make sure that access is really denied if we need to deny it.
	 *
	 * @test
	 */
	public function mayParticipateReturnsFalseIfAnyVoterReturnsFalse() {
		$that = $this;
		$mockVoterFactory = function($returnValue) use ($that) {
			$mockedAccessVoter = $that->getMockForAbstractClass('AstaKit\FriWahl\Core\Security\Voting\BaseVotingAccessVoter', array(), '', FALSE);
			$mockedAccessVoter->expects($this->any())->method('mayParticipate')->will($this->returnValue($returnValue));
			$mockedAccessVoter->expects($this->any())->method('canVote')->will($this->returnValue(TRUE));
			return $mockedAccessVoter;
		};

		$mockedAccessVoters = array(
			$mockVoterFactory(TRUE),
			$mockVoterFactory(FALSE),
			$mockVoterFactory(TRUE),
		);

		$accessManager = new VotingAccessManager();
		$accessManager->setAccessVoters($mockedAccessVoters);
		$this->inject($accessManager, 'log', $this->getMock('TYPO3\Flow\Log\SystemLoggerInterface'));

		$this->assertFalse($accessManager->mayParticipate($this->getMockedVoter(), $this->getMockedVoting()));
	}

}
