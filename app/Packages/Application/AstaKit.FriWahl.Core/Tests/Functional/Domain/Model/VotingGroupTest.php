<?php
namespace AstaKit\FriWahl\Core\Tests\Functional\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\SingleListVoting;
use AstaKit\FriWahl\Core\Domain\Model\VotingGroup;
use TYPO3\Flow\Tests\FunctionalTestCase;


/**
 *
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingGroupTest extends FunctionalTestCase {

	/**
	 * {@inheritDoc}
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockElection() {
		return new Election(uniqid(), uniqid());
	}

	/**
	 * @test
	 */
	public function votingsAddedToAGroupCanBeResurrectedFromDatabase() {
		$election = $this->mockElection();
		$votingGroup = new VotingGroup(uniqid(), $election);

		$voting = new SingleListVoting(uniqid(), $election);
		$votingGroup->addVoting($voting);

		$groupIdentity = $this->persistenceManager->getIdentifierByObject($votingGroup);

		// adding the election seems to be enough here, though there are no cascade operators…
		$this->persistenceManager->add($election);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var VotingGroup $group */
		$group = $this->persistenceManager->getObjectByIdentifier($groupIdentity, 'AstaKit\\FriWahl\\Core\\Domain\\Model\\VotingGroup');

		$this->assertEquals(1, count($group->getVotings()));
	}

}
 