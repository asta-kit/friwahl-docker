<?php
namespace AstaKit\FriWahl\AdminInterface\Command;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;


/**
 * Command controller for managing ballot boxes.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class BallotBoxCommandController extends CommandController {

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @param Election $election
	 * @param string $identifier
	 * @param string $name
	 * @param string $sshPublicKey
	 */
	public function createCommand(Election $election, $identifier, $name, $sshPublicKey = '') {
		if ($election->hasBallotBox($identifier)) {
			$this->outputLine('Ballot box ' . $identifier . ' already exists in election ' . $election->getName());
			return;
		}

		$ballotBox = new BallotBox($identifier, $name, $election);
		if ($sshPublicKey != '') {
			$ballotBox->setSshPublicKey($sshPublicKey);
		}

		$this->persistenceManager->add($ballotBox);

		$this->outputLine('Ballot box ' . $identifier . ' successfully created.');
	}

	/**
	 * Lists all ballot boxes and their status
	 *
	 * @param Election $election
	 */
	public function listCommand(Election $election) {
		$this->outputLine(str_pad('Ballot box', 40, ' ', STR_PAD_BOTH) . ' |   Status   | Pending | Committed |  Total  |');

		/** @var $ballotBox BallotBox */
		foreach ($election->getBallotBoxes() as $ballotBox) {
			$pendingVotes = $ballotBox->getQueuedVotesCount();
			$committedVotes = $ballotBox->getCommittedVotesCount();
			$totalVotes = $pendingVotes + $committedVotes;

			$this->outputLine(
				str_pad($ballotBox->getIdentifier(), 40, ' ', STR_PAD_RIGHT) . ' | '
				. str_pad($ballotBox->getStatusText(), 10, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($pendingVotes, 7, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($committedVotes, 9, ' ', STR_PAD_LEFT) . ' | '
				. str_pad($totalVotes, 7, ' ', STR_PAD_LEFT) . ' | ');
		}
	}

	/**
	 * Emits a ballot box, making it available for voting.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function emitCommand(BallotBox $ballotBox) {
		try {
			$ballotBox->emit();

			$this->persistenceManager->update($ballotBox);
		} catch (\Exception $e) {
			$this->outputLine('Urne konnte nicht ausgegeben werden: %s (%d)', array($e->getMessage(), $e->getCode()));
		}

		$this->outputLine('Urne %s erfolgreich ausgegeben.', array($ballotBox->getIdentifier()));
	}

	/**
	 * Returns a ballot box, locking it for voting sessions.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function returnCommand(BallotBox $ballotBox) {
		try {
			$ballotBox->returnBox();

			$this->persistenceManager->update($ballotBox);
		} catch (\Exception $e) {
			$this->outputLine('Urne konnte nicht zurückgenommen werden: %s (%d)', array($e->getMessage(), $e->getCode()));

			return;
		}

		$this->outputLine('Urne %s erfolgreich zurückgenommen.', array($ballotBox->getIdentifier()));
	}

}
