<?php
namespace AstaKit\FriWahl\AdminInterface\Controller;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;


/**
 * Default controller for the admin interface.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class StandardController extends ActionController {

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;


	/**
	 *
	 */
	public function indexAction() {
		$elections = $this->electionRepository->findAll();
		$this->view->assign('elections', $elections);
	}
}
