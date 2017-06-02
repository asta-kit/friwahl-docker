<?php
namespace AstaKit\FriWahl\Core\Command;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Repository\VoteRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;


/**
 * CLI commands related to votes
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VoteCommandController extends CommandController {

	/**
	 * @var VoteRepository
	 * @Flow\Inject
	 */
	protected $voteRepository;

	/**
	 * @param Election $election
	 */
	public function statsCommand(Election $election) {
		$stats = array();
		/** @var $voting Voting */
		foreach ($election->getVotings() as $voting) {
			$queued = $this->voteRepository->countQueuedByVoting($voting);
			$committed = $this->voteRepository->countCommittedByVoting($voting);

			$stats[$voting->getName()] = array(
				'queued' => $queued,
				'committed' => $committed,
				'total' => $queued + $committed,
			);
		}

		$this->outputLine(str_pad('Voting', 40, ' ', STR_PAD_BOTH) . ' | Committed |  Queued  |  Total  |');
		$this->outputLine(str_pad('', 40, '-')                     . '-+-----------+----------+---------+');
		foreach ($stats as $voting => $counts) {
			$this->outputLine(
				str_pad($voting, 40, ' ', STR_PAD_RIGHT) . ' | '
				. str_pad($counts['committed'], 9, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($counts['queued'], 8, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($counts['total'], 7, ' ', STR_PAD_LEFT) . ' | '
			);
		}
	}


	public function showQueueCommand() {

	}

}
