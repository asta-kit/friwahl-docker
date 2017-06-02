<?php
namespace AstaKit\FriWahl\Core\Tests\Functional\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Domain\Model\SingleListVoting;
use AstaKit\FriWahl\Core\Domain\Model\Vote;
use AstaKit\FriWahl\Core\Domain\Service\VotingService;
use TYPO3\Flow\Tests\FunctionalTestCase;


/**
 * Test case for the voting service.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingServiceTest extends FunctionalTestCase {

	/**
	 * {@inheritDoc}
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var Election
	 */
	protected $election;

	protected function createElection() {
		$this->election = new Election('test-' . uniqid(), uniqid());
		$this->persistenceManager->add($this->election);
	}

	/**
	 * @param int $votingsCount
	 * @return EligibleVoter, BallotBox, array<Voting>
	 */
	protected function createVotingEnvironment($votingsCount = 1) {
		$this->createElection();

		$votings = array();
		for ($i = 0; $i < $votingsCount; ++$i) {
			$voting = new SingleListVoting('voting-' . $i, $this->election);
			$this->persistenceManager->add($voting);
			$votings[] = $voting;
		}

		$voter = new EligibleVoter($this->election, uniqid(), uniqid());
		$this->persistenceManager->add($voter);

		$ballotBox = new BallotBox(uniqid(), uniqid(), $this->election);
		$this->persistenceManager->add($ballotBox);

		$this->persistenceManager->persistAll();

		return array($voter, $ballotBox, $votings);
	}

	/**
	 * @test
	 */
	public function voteIsStoredInDatabaseAfterItHasBeenCommitted() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment();

		$votingService = new VotingService();
		$vote = $votingService->createVote($ballotBox, $voter, $votings[0]);

		$this->assertInstanceOf('AstaKit\FriWahl\Core\Domain\Model\Vote', $vote);
	}

	/**
	 * @test
	 */
	public function multipleVotesAreStoredInDatabaseAfterTheyHaveBeenCommitted() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);

		$votingService = new VotingService();
		$votes = $votingService->createVotes($ballotBox, $voter, $votings);

		$this->assertCount(2, $votes);
		$this->assertInstanceOf('AstaKit\FriWahl\Core\Domain\Model\Vote', $votes[0]);
	}

	/**
	 * @test
	 */
	public function votingIsDeniedIfVoterHasAlreadyParticipatedInVoting() {
		$this->setExpectedException('RuntimeException', '', 1402411794);

		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(1);

		$votingService = new VotingService();
		$votingService->createVote($ballotBox, $voter, $votings[0]);

		$this->persistenceManager->persistAll();

		$votingService->createVote($ballotBox, $voter, $votings[0]);
	}

	/**
	 * @test
	 */
	public function votesAreAvailableInVoterObjectAfterVoting() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(1);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		$votingService = new VotingService();
		$votingService->createVote($ballotBox, $voter, $votings[0]);

		$votes = $voter->getVotes();

		$this->assertCount(1, $votes);

		// test if the votes are available even after clearing the persistence state – this tests if the objects are
		// correctly fetched from the database during property mapping (the code above only added them through
		// EligibleVoter::addVote())
		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterId, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
		$votes = $voter->getVotes();

		$this->assertCount(1, $votes, 'Vote was apparently not saved in the database, or there is more than one vote saved');
	}

	/**
	 * @test
	 */
	public function noVoteIsCreatedIfVoterHasAlreadyParticipatedInAtLeastOneVoting() {
		$queue = msg_get_queue(1);
		print_r(msg_send($queue, 1, array('foo' => 'bar')));

		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		// cast a vote in the second voting; when testing all votings later on, the first will go through, but the
		// second will fail (and thus all must fail)
		$votingService = new VotingService();
		$votingService->createVote($ballotBox, $voter, $votings[1]);

		try {
			$votingService->createVotes($ballotBox, $voter, $votings);
		} catch (\RuntimeException $e) {
			// silently ignore the exception because we want to later check if any vote got into the database
			// accidentally despite the exception.
		}

		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterId, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
		$votes = $voter->getVotes();

		$this->assertCount(1, $votes, 'Not only the first vote was saved in the database.');
	}

	/**
	 * @test
	 */
	public function cancellingVotesRemovesUncommittedVotesOfVoterFromVoterObject() {
		/** @var $voter EligibleVoter */
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		$votingService = new VotingService();
		$votingService->createVotes($ballotBox, $voter, $votings);
		// The votes are now available in the database, so try to cancel them now

		$votingService->cancelPendingVotesForVoter($ballotBox, $voter);
		$votes = $voter->getVotes();

		$this->assertCount(0, $votes, 'Cancelled votes were not removed from voter object.');
	}

	/**
	 * @test
	 */
	public function cancellingVotesRemovesUncommittedVotesOfVoterFromDatabase() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		$votingService = new VotingService();
		$votingService->createVotes($ballotBox, $voter, $votings);
		// The votes are now available in the database, so try to cancel them now

		$votingService->cancelPendingVotesForVoter($ballotBox, $voter);

		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterId, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
		$votes = $voter->getVotes();

		$this->assertCount(0, $votes, 'Cancelled votes were not removed from database.');
	}

	/**
	 * @test
	 */
	public function cancellingVotesDoesNotRemoveVotingFromDatabase() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		$votingService = new VotingService();
		$votingService->createVotes($ballotBox, $voter, $votings);
		// The votes are now available in the database, so try to cancel them now

		$votingService->cancelPendingVotesForVoter($ballotBox, $voter);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterId, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
		$votes = $voter->getVotes();

		$this->assertCount(0, $votes, 'Cancelled votes were not removed from database.');
	}

	/**
	 * @test
	 */
	public function committingVotesReturnsCorrectVotesCount() {
		list($voter, $ballotBox, $votings) = $this->createVotingEnvironment(2);
		$voterId = $this->persistenceManager->getIdentifierByObject($voter);

		// cast a vote in the second voting; when testing all votings later on, the first will go through, but the
		// second will fail (and thus all must fail)
		$votingService = new VotingService();

		$votingService->createVotes($ballotBox, $voter, $votings);
		// The votes are now available in the database, so try to cancel them now

		$votingService->commitPendingVotesForVoter($ballotBox, $voter);

		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterId, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');
		$votes = $voter->getVotes();

		$this->assertCount(2, $votes, 'Committed votes are not in database.');
		$this->assertTrue($votes[0]->isCommitted());
		$this->assertTrue($votes[1]->isCommitted());
	}

}
