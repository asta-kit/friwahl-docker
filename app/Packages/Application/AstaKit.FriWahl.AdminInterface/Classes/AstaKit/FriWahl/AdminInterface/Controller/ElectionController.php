<?php
namespace AstaKit\FriWahl\AdminInterface\Controller;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\ElectionPeriod;
use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for managing elections.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ElectionController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('elections', $this->electionRepository->findAll());
	}

	/**
	 * Displays a form for creating a new election.
	 *
	 * @return void
	 */
	public function newAction() {
	}

	/**
	 * @param Election $election
	 * @return void
	 */
	public function createAction(Election $election) {
		$this->electionRepository->add($election);

		$this->forward('show', NULL, NULL, array('election' => $election));
	}

	/**
	 * @param Election $election
	 * @return void
	 */
	public function showAction(Election $election) {
		$this->view->assign('election', $election);
	}

	/**
	 * Displays a form for creating a new election period.
	 *
	 * @param Election $election
	 */
	public function newPeriodAction(Election $election) {
		$this->view->assign('election', $election);
	}

	/**
	 * Creates an election period and redirects to the election overview.
	 *
	 * @param ElectionPeriod $period
	 * @return void
	 */
	public function createPeriodAction(ElectionPeriod $period) {
		$election = $period->getElection();

		$this->electionRepository->update($election);

		$this->redirect('show', NULL, NULL, array('election' => $election));
	}

}