<?php
namespace AstaKit\FriWahl\VoterDirectory\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory".*
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use AstaKit\FriWahl\VoterDirectory\Domain\Model\VoterSearch;
use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for the voter directory
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class DirectoryController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;

	/**
	 * @param Election $election
	 * @return void
	 */
	public function indexAction($election) {
		$this->view->assign('voterCount', $this->electionRepository->countVotersByElection($election));
		$this->view->assign('election', $election);
	}

	/**
	 * @param VoterSearch $search
	 */
	public function searchAction(VoterSearch $search) {
		$this->view->assign('search', $search);
	}

	/**
	 * @param EligibleVoter $voter
	 * @param VoterSearch $search
	 */
	public function showAction(EligibleVoter $voter, VoterSearch $search = NULL) {
		$this->view->assign('voter', $voter);
		$this->view->assign('search', $search);
	}

}