<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Relation class for a candidate in a voting proposal. Having this in a separate class is necessary because we need
 * to maintain a strict order of candidates, which is currently not supported by Doctrine
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\ValueObject
 *
 * TODO add a unique constraint spanning the proposal and position, to ensure that each position is only used once per proposal
 */
class VotingProposalCandidate {

	/**
	 * @var VotingProposal
	 * @ORM\ManyToOne(inversedBy="candidates")
	 */
	protected $proposal;

	/**
	 * @var EligibleVoter
	 * @ORM\ManyToOne
	 */
	protected $candidate;

	/**
	 * The position of the candidate in the proposal.
	 *
	 * @var integer
	 */
	protected $position;

	/**
	 * @param integer $position
	 * @param VotingProposal $proposal
	 * @param EligibleVoter $candidate
	 */
	public function __construct($position, $proposal, $candidate) {
		$this->position = $position;
		$this->proposal = $proposal;
		$this->candidate = $candidate;
	}

	/**
	 * @return EligibleVoter
	 */
	public function getCandidate() {
		return $this->candidate;
	}

	/**
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @return VotingProposal
	 */
	public function getProposal() {
		return $this->proposal;
	}

}
