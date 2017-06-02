<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Relation class for a supporter of a voting proposal. Having this in a separate class is necessary because we need
 * to maintain a strict order of supporters, which is currently not supported by Doctrine.
 *
 * Additionally, we could store a status with this supporter—e.g. for automated checks if they are allowed to support
 * the proposal—and even more data if necessary.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\ValueObject
 *
 * TODO add a unique constraint spanning the proposal and position, to ensure that each position is only used once per proposal
 */
class VotingProposalSupporter {

	/**
	 * @var VotingProposal
	 * @ORM\ManyToOne(inversedBy="candidates")
	 */
	protected $proposal;

	/**
	 * @var EligibleVoter
	 * @ORM\ManyToOne
	 */
	protected $supporter;

	/**
	 * The position of the candidate in the proposal.
	 *
	 * @var integer
	 */
	protected $position;

	/**
	 * @param integer $position
	 * @param VotingProposal $proposal
	 * @param EligibleVoter $supporter
	 */
	public function __construct($position, $proposal, $supporter) {
		$this->position = $position;
		$this->proposal = $proposal;
		$this->supporter = $supporter;
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

	/**
	 * @return EligibleVoter
	 */
	public function getSupporter() {
		return $this->supporter;
	}
}
