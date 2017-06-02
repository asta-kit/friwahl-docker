<?php
namespace AstaKit\FriWahl\AdminInterface\Controller;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Repository\BallotBoxRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;


/**
 * Controller for managing ballot boxes
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class BallotBoxController extends ActionController {

	/**
	 * @var BallotBoxRepository
	 * @Flow\Inject
	 */
	protected $ballotBoxRepository;

	/**
	 * @param Election $election
	 * @return void
	 */
	public function newAction(Election $election) {
		$this->view->assign('election', $election);
	}

	/**
	 * @param BallotBox $ballotBox
	 * @return void
	 */
	public function createAction(BallotBox $ballotBox) {
		$this->ballotBoxRepository->add($ballotBox);

		$this->redirect('show', 'Election', NULL, array('election' => $ballotBox->getElection()));
	}

	/**
	 * @param BallotBox $ballotBox
	 * @return void
	 */
	public function showAction(BallotBox $ballotBox) {
		$this->view->assign('ballotBox', $ballotBox);
	}

	/**
	 * @param BallotBox $ballotBox
	 * @return void
	 */
	public function editAction(BallotBox $ballotBox) {
		$this->view->assign('ballotBox', $ballotBox);
	}

	/**
	 * @param BallotBox $ballotBox
	 * @return void
	 */
	public function updateAction(BallotBox $ballotBox) {
		$this->ballotBoxRepository->update($ballotBox);
		$this->addFlashMessage('Ballot box updated successfully.');

		$this->forward('show', 'Election', NULL, array('election' => $ballotBox->getElection()));
	}

	/**
	 * Emit a ballot box, making it available for voting.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function emitAction(BallotBox $ballotBox) {
		$ballotBox->emit();
		$this->ballotBoxRepository->update($ballotBox);

		$this->addFlashMessage('Urne wurde ausgegeben.');

		$this->redirect('show', NULL, NULL, array('ballotBox' => $ballotBox));
	}

	/**
	 * Return a ballot box, making it unavailable for voting.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function returnAction(BallotBox $ballotBox) {
		$ballotBox->returnBox();
		$this->ballotBoxRepository->update($ballotBox);

		$this->addFlashMessage('Urne wurde zurückgenommen.');

		$this->redirect('show', NULL, NULL, array('ballotBox' => $ballotBox));
	}

}
