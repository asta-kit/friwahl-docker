<?php
namespace AstaKit\FriWahl\AdminInterface\Controller;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\VotingGroup;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\Argument;


/**
 * Controller for managing votings.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingController extends ActionController {

	/**
	 * The settings for all AstaKit.FriWahl packages; this is a hack because the mechanism that writes
	 * the settings injector (in Flow's Dependency Injection Builder) does not natively support injecting a complete
	 * package's setting; therefore we split the package key to get the settings
	 *
	 * @var array
	 * @Flow\Inject(package="AstaKit",setting="FriWahl")
	 */
	protected $globalSettings;

	/**
	 * Displays the given voting. See the election controller for a list of votings; this is only displayed there
	 * because such a list does not make sense outside the context of a voting.
	 *
	 * @param Voting $voting
	 * @return void
	 */
	public function showAction(Voting $voting) {
		$this->view->assign('voting', $voting);
	}

	/**
	 * Displays the form for creating a new voting. In fact this is kind of a two-step form, the first step is just
	 * for selecting the voting type to create.
	 *
	 * @param Election $election
	 * @param VotingGroup $group The group this voting should belong to. Optional
	 * @param string $type
	 * @throws \InvalidArgumentException
	 */
	public function newAction(Election $election = NULL, VotingGroup $group = NULL, $type = NULL) {
		if (!$election && !$group) {
			throw new \InvalidArgumentException('Election or voting group has to be specified');
		}
		if (!$election) {
			$election = $group->getElection();
			$this->view->assign('container', $group);
		} else {
			$this->view->assign('container', $election);
		}
		if ($group) {
			$this->view->assign('group', $group);
		} else {
			$this->view->assign('election', $election);
		}
		$this->view->assign('type', $type);

		if ($type !== NULL) {
			$this->view->assign('typeConfiguration', $this->globalSettings['votingTypes'][$type]);
		}
	}

	/**
	 * Initializes the action to persist a newly created voting.
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		/** @var Argument $argument */
		$argument = $this->arguments->getArgument('voting');

		$argument->getPropertyMappingConfiguration()->allowOverrideTargetType();
	}

	/**
	 * Persists a newly created voting.
	 *
	 * @param Voting $voting
	 * @param VotingGroup $group The group the voting belongs to
	 */
	public function createAction(Voting $voting, VotingGroup $group = NULL) {
		$this->persistenceManager->add($voting);

		if ($group) {
			$group->addVoting($voting);
			$this->persistenceManager->update($group);
		}

		$this->persistenceManager->persistAll();

		$this->redirect('show', NULL, NULL, array('voting' => $voting));
	}
}
