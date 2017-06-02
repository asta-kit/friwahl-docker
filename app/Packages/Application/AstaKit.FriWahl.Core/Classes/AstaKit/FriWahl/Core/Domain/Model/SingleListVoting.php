<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * A voting with a single list. For this voting, only voting for candidates is possible; the list as a whole cannot
 * be voted for, as this would be pretty pointless with only one alternative.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class SingleListVoting extends Voting {

	/**
	 * The list for this voting.
	 *
	 * @var VotingProposal
	 * @ORM\OneToOne
	 */
	protected $list;

	/**
	 * If the name of the list should be shown e.g. when displaying the results for the public. It might be desirable
	 * to hide it because it is
	 *
	 * @var boolean
	 */
	protected $showListName;

	/**
	 * Returns the type of this record.
	 *
	 * @return string
	 */
	public function getType() {
		return 'SingleList';
	}

	/**
	 * Returns the list of candidates for this voting.
	 *
	 * @return \AstaKit\FriWahl\Core\Domain\Model\VotingProposal
	 */
	public function getList() {
		return $this->list;
	}

	/**
	 * @param \AstaKit\FriWahl\Core\Domain\Model\VotingProposal $list
	 */
	public function setList($list) {
		$this->list = $list;
	}

	/**
	 * @param boolean $showListName
	 */
	public function setShowListName($showListName) {
		$this->showListName = $showListName;
	}

	/**
	 * @return boolean
	 */
	public function getShowListName() {
		return $this->showListName;
	}

	/**
	 * @param \AstaKit\FriWahl\Core\Security\Voting\VotingAccessManager $votingAccessManager
	 */
	public function setVotingAccessManager($votingAccessManager) {
		$this->votingAccessManager = $votingAccessManager;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Security\Voting\VotingAccessManager
	 */
	public function getVotingAccessManager() {
		return $this->votingAccessManager;
	}

}
