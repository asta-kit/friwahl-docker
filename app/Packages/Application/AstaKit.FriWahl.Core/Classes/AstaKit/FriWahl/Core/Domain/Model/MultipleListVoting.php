<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;


/**
 * A multiple-list voting, i.e. a voting with different lists of candidates for which a voter can vote.
 *
 * By default both lists and single candidates will be open for votes, but it will also be possible to restrict voting
 * to lists only (= closed lists voting).
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class MultipleListVoting extends Voting {

	/**
	 * @var Collection<VotingProposal>
	 * @ORM\OneToMany(mappedBy="proposal")
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $lists;

	public function __construct($name, Election $election = NULL, VotingGroup $votingGroup = NULL) {
		parent::__construct($name, $election, $votingGroup);

		$this->lists = new ArrayCollection();
	}

	/**
	 * Returns the type of this record.
	 *
	 * @return string
	 */
	public function getType() {
		return 'MultipleList';
	}

	/**
	 * Returns the lists of candidates.
	 *
	 * @return Collection<VotingProposal>
	 */
	public function getLists() {
		return $this->lists;
	}

	/**
	 * Adds a new list of candidates to this voting.
	 *
	 * @param VotingProposal $list
	 */
	public function addList(VotingProposal $list) {
		// TODO set position to last one

		$this->lists->add($list);
	}
}
