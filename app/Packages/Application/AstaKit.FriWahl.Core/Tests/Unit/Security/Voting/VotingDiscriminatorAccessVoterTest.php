<?php
namespace AstaKit\FriWahl\Core\Tests\Unit\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Security\Voting\VotingDiscriminatorAccessVoter;
use TYPO3\Flow\Tests\UnitTestCase;


/**
 * Test case for the discriminator-based access voter
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingDiscriminatorAccessVoterTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function canVoteReturnsFalseIfNoDiscriminatorIsDefinedForVoting() {
		$accessVoter = new VotingDiscriminatorAccessVoter();

		$mockedVoter = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
		$mockedVoting = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue(''));

		$this->assertFalse($accessVoter->canVote($mockedVoter, $mockedVoting));
	}

	/**
	 * @test
	 */
	public function canVoteReturnsTrueIfDiscriminatorIsDefinedForVoting() {
		$accessVoter = new VotingDiscriminatorAccessVoter();

		$mockedVoter = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
		$mockedVoting = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminator')->will(
			$this->returnValue($this->getMock('AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator', array(),array(), '', FALSE))
		);

		$this->assertTrue($accessVoter->canVote($mockedVoter, $mockedVoting));
	}

	/**
	 * @test
	 */
	public function mayParticipateReturnsTrueForMatchingDiscriminator() {
		$discriminatorIdentifier = uniqid();
		$discriminatorValue = uniqid();

		$mockedVoter = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
		$mockedVoting = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
		$discriminator = new VoterDiscriminator($mockedVoter, $discriminatorIdentifier, $discriminatorValue);
		$mockedVoter->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminator));

		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminationMode')->will($this->returnValue(Voting::DISCRIMINATION_MODE_ALLOW));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminatorIdentifier));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminatorValues')->will($this->returnValue(array($discriminatorValue)));

		$accessVoter = new VotingDiscriminatorAccessVoter();
		$this->assertTrue($accessVoter->mayParticipate($mockedVoter, $mockedVoting));
	}

	/**
	 * @test
	 */
	public function mayParticipateReturnsFalseIfDiscriminationModeIsDenyAndDiscriminatorDoesMatch() {
		$discriminatorIdentifier = uniqid();
		$discriminatorValue = uniqid();

		$mockedVoter = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
		$mockedVoting = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
		$discriminator = new VoterDiscriminator($mockedVoter, $discriminatorIdentifier, $discriminatorValue);
		$mockedVoter->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminator));

		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminationMode')->will($this->returnValue(Voting::DISCRIMINATION_MODE_DENY));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminatorIdentifier));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminatorValues')->will($this->returnValue(array($discriminatorValue)));

		$accessVoter = new VotingDiscriminatorAccessVoter();
		$this->assertFalse($accessVoter->mayParticipate($mockedVoter, $mockedVoting));
	}

	/**
	 * @test
	 */
	public function mayParticipateReturnsTrueIfDiscriminationModeIsDenyAndDiscriminatorDoesNotMatch() {
		$discriminatorIdentifier = uniqid();

		$mockedVoter = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\EligibleVoter', array(), array(), '', FALSE);
		$mockedVoting = $this->getMock('AstaKit\FriWahl\Core\Domain\Model\Voting', array(), array(), '', FALSE);
		$discriminator = new VoterDiscriminator($mockedVoter, $discriminatorIdentifier, uniqid());
		$mockedVoter->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminator));

		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminationMode')->will($this->returnValue(Voting::DISCRIMINATION_MODE_DENY));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminator')->will($this->returnValue($discriminatorIdentifier));
		$mockedVoting->expects($this->atLeastOnce())->method('getDiscriminatorValues')->will($this->returnValue(array(uniqid())));

		$accessVoter = new VotingDiscriminatorAccessVoter();
		$this->assertTrue($accessVoter->mayParticipate($mockedVoter, $mockedVoting));
	}
}
