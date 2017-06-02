<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A list of candidates for a voting, plus the people who support this proposal.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class VotingProposal {

	/**
	 * The (long) name of this proposal. Only relevant if this proposal belongs to a voting with multiple proposals,
	 * e.g. a MultipleListVoting.
	 *
	 * @var string
	 * @ Flow\Validate(type="NotEmpty")
	 */
	protected $name = '';

	/**
	 * The short name for this proposal, e.g. an abbreviation for the list name.
	 *
	 * @var string
	 * @ Flow\Validate(type="NotEmpty")
	 */
	protected $shortName = '';

	/**
	 * The position of this list within the voting. Is used e.g. for the position within a list of results
	 *
	 * @var integer
	 *
	 * TODO set this automatically based on the last value used
	 */
	protected $position = 0;

	/**
	 * @var Voting
	 * @ORM\ManyToOne(inversedBy="proposals")
	 */
	protected $voting;

	/**
	 * The candidates for this proposal.
	 *
	 * @var Collection<VotingProposalCandidate>
	 * @ORM\OneToMany(mappedBy="proposal")
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $candidates;

	/**
	 * The supporters for this proposal.
	 *
	 * @var Collection<VotingProposalSupporter>
	 * @ORM\OneToMany(mappedBy="proposal")
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $supporters;

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Voting
	 */
	public function getVoting() {
		return $this->voting;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getCandidates() {
		return $this->candidates;
	}

	/**
	 * @param VotingProposalCandidate $candidate
	 */
	public function addCandidate(VotingProposalCandidate $candidate) {
		$this->candidates->add($candidate);
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSupporters() {
		return $this->supporters;
	}

	/**
	 * Checks if the given voter is a supporter of this proposal.
	 *
	 * @param EligibleVoter $voter
	 * @return bool
	 */
	public function isSupporter(EligibleVoter $voter) {
		return $this->supporters->contains($voter);
	}

	/**
	 * Adds a supporter for this proposal.
	 *
	 * @param EligibleVoter $supporter
	 * @param integer $position
	 */
	public function addSupporter(EligibleVoter $supporter, $position = NULL) {
		$supporterAssociation = new VotingProposalSupporter($position, $this, $supporter);

		// We're not using the position here for adding because it will be implicitly used by Doctrine to order the
		// collection when loading the objects again. The worst thing that could happen is that a record is positioned
		// at the wrong location while rendering the document
		$this->supporters->add($supporterAssociation);
	}

}
 