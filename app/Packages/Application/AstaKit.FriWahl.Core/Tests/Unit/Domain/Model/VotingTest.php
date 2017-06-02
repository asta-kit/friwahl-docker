<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Domain\Model\VotingGroup;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the abstract voting class.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingTest extends UnitTestCase {

	protected function createDummy($class) {
		return $this->getMock($class, array(), array(), '', FALSE);
	}

	/**
	 * @return EligibleVoter
	 */
	protected function getMockedVoter($election) {
		return $this->getMock('AstaKit\\FriWahl\\Core\\Domain\\Model\\EligibleVoter', array(), array($election, uniqid(), uniqid()));
	}

	/**
	 * @return Election
	 */
	protected function getElectionDummy() {
		return $this->createDummy('AstaKit\\FriWahl\\Core\\Domain\\Model\\Election');
	}

	/**
	 * @test
	 */
	public function constructorThrowsExceptionIfNeitherElectionNorVotingGroupIsSet() {
		$this->setExpectedException('Exception', '', 1403516217);

		/** @var Voting $voting */
		$voting = $this->getMockForAbstractClass('AstaKit\\FriWahl\\Core\\Domain\\Model\\Voting', array(
			uniqid()
		));
	}

	/**
	 * @test
	 */
	public function constructorThrowsExceptionIfBothElectionAndVotingGroupAreGiven() {
		$this->setExpectedException('Exception', '', 1403516216);

		$election = $this->getElectionDummy();
		$votingGroup = new VotingGroup('', $election);

		/** @var Voting $voting */
		$voting = $this->getMockForAbstractClass('AstaKit\\FriWahl\\Core\\Domain\\Model\\Voting', array(
			uniqid(), $election, $votingGroup
		));
	}

	/**
	 * @test
	 */
	public function electionForVotingInGroupIsReturnedFromVotingGroupIfGroupIsSet() {
		$election = $this->getElectionDummy();
		$votingGroup = new VotingGroup('', $election);

		/** @var Voting $voting */
		$voting = $this->getMockForAbstractClass('AstaKit\\FriWahl\\Core\\Domain\\Model\\Voting', array(
			uniqid(), NULL, $votingGroup
		));

		$this->assertSame($election, $voting->getElection());
	}
}
