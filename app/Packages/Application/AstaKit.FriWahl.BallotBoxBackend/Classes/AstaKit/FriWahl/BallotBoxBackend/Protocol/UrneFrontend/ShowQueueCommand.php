<?php
namespace AstaKit\FriWahl\BallotBoxBackend\Protocol\UrneFrontend;

/*                                                                                    *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.BallotBoxBackend".  *
 *                                                                                    *
 *                                                                                    */

use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use TYPO3\Flow\Annotations as Flow;


/**
 * Command to show all queued voters with their queued votes.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ShowQueueCommand extends AbstractCommand implements ListingCommand {

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @param array $parameters
	 */
	public function process(array $parameters = NULL) {
		$pendingVotes = $this->ballotBox->getQueuedVotes();

		$electionVotings = $this->ballotBox->getElection()->getVotings();

		$queue = array();
		foreach ($pendingVotes as $vote) {
			$voterId = $vote->getVoter()->getIdentifier();

			if (!isset($queue[$voterId])) {
				$queue[$voterId] = array();
			}

			$votingId = $electionVotings->indexOf($vote->getVoting());
			if ($votingId === FALSE) {
				throw new \RuntimeException('Voting not found!');
			}
			// voting IDs are one- and not zero-based
			++$votingId;
			$queue[$voterId][] = $votingId;
		}

		ksort($queue);

		foreach ($queue as $voterId => $elements) {
			$this->addResultLine($voterId . ' ' . implode(' ', $elements));
		}
	}

}
