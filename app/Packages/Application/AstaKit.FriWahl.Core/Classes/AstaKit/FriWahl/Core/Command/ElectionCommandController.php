<?php
namespace AstaKit\FriWahl\Core\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\VotingTurnout;
use AstaKit\FriWahl\Core\Domain\Service\TurnoutService;
use Doctrine\Common\Util\Debug;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Error\Debugger;


/**
 * CLI commands for managing an election
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ElectionCommandController extends CommandController {

	/**
	 * @var TurnoutService
	 * @Flow\Inject
	 */
	protected $turnoutService;

	/**
	 * Displays the turnout for all votings in the election.
	 *
	 * @param Election $election
	 */
	public function turnoutCommand(Election $election) {
		$this->outputLine(str_pad('Voting', 55, ' ', STR_PAD_BOTH) . ' |  Voters  |  Votes  |  % Part.  |');
		$this->outputLine(str_pad('', 55, '-')                     . '-+----------+---------+-----------+');

		/** @var $voting Voting */
		foreach ($election->getVotings() as $voting) {
			$turnout = $this->turnoutService->getTurnoutForVoting($voting);

			$this->printTurnoutLine($voting->getName(), $turnout->getTotalVoterCount(), $turnout->getTotalVotesCount(), $turnout->getTotalTurnoutPercent());

			if ($turnout->hasSubResults()) {
				/** @var $result VotingTurnout */
				foreach ($turnout->getSubResults() as $result) {
					$this->printTurnoutLine('- ' . $result->getVoting()->getName(),
						$result->getTotalVoterCount(),
						$result->getTotalVotesCount(),
						$result->getTotalTurnoutPercent()
					);
				}
			}
		}
	}

	/**
	 * @param string $name
	 * @param int $voters
	 * @param int $votes
	 * @param float $turnout
	 */
	protected function printTurnoutLine($name, $voters, $votes, $turnout) {
		$this->outputLine(
			str_pad($name, 55, ' ') . ' | '
			. str_pad($voters, 8, ' ', STR_PAD_LEFT) . ' | '
			. str_pad($votes, 7, ' ', STR_PAD_LEFT) . ' | '
			. str_pad(
				round($turnout, 2),
				9, ' ', STR_PAD_LEFT
			) . ' | '
		);
	}

}
 