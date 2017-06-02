<?php
namespace AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontend;

use AstaKit\FriWahl\Core\Domain\Service\VotingService;
use TYPO3\Flow\Annotations as Flow;


/**
 * Creates new Vote objects for the given voter and votings
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class InsertQueueElementCommand extends VoterRelatedCommand {

	/**
	 * @var VotingService
	 * @Flow\Inject
	 */
	protected $votingService;

	/**
	 * @param array $parameters
	 */
	public function process(array $parameters = NULL) {
		$voterId = array_shift($parameters);
		$voter = $this->findVoter($voterId);

		$electionVotings = $this->ballotBox->getElection()->getVotings();
		$votedVotings = array();
		// The voting numbers returned by the client are the indexes of the votings in the election, just
		// one-based instead of zero-based. See ShowElectionsCommand for details.
		foreach ($parameters as $votingNumber) {
			$votedVotings[] = $electionVotings->offsetGet($votingNumber - 1);
		}

		$this->votingService->createVotes($this->ballotBox, $voter, $votedVotings);
	}

}
