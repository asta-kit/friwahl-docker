<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * A vote given by a voter in a voting.
 *
 * NOTE: Objects of this class are *always* created automatically by stored procedures. Do not create them manually!
 *
 * TODO secure the chain of votes by creating a "checksum" for each vote based on the previous vote (in the database)
 *      and all values. This should be implemented in the register_vote stored procedure.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="voter_voting", columns={"voter", "voting"})})
 */
class Vote {

	/**
	 * @var int
	 * @ORM\GeneratedValue
	 * @ORM\Id
	 * @Flow\Identity
	 */
	protected $id;

	/**
	 * @var EligibleVoter
	 * @ORM\ManyToOne(inversedBy="voter", cascade={})
	 */
	protected $voter;

	/**
	 * @var Voting
	 * @ORM\ManyToOne(cascade={})
	 */
	protected $voting;

	/**
	 * @var BallotBox
	 * @ORM\ManyToOne(cascade={})
	 */
	protected $ballotBox;

	/**
	 * @var \DateTime
	 */
	protected $dateCreated;

	/**
	 * @var \DateTime
	 */
	protected $dateCommitted;

	/**
	 * @var integer
	 */
	protected $status = self::STATUS_QUEUED;

	/** Vote has been queued, voter is filling out ballot */
	const STATUS_QUEUED = 1;
	/** Vote has been committed, voter has (or is about to) put ballot into ballot box */
	const STATUS_COMMITTED = 2;


	public function __construct() {
		throw new \RuntimeException('Vote objects cannot be created manually. Use VotingService instead.', 1402420100);
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\EligibleVoter
	 */
	public function getVoter() {
		return $this->voter;
	}

	/**
	 * @return Voting
	 */
	public function getVoting() {
		return $this->voting;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\BallotBox
	 */
	public function getBallotBox() {
		return $this->ballotBox;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateCreated() {
		return $this->dateCreated;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateCommitted() {
		return $this->dateCommitted;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	public function isCommitted() {
		return $this->status === self::STATUS_COMMITTED;
	}

	public function isQueued() {
		return $this->status === self::STATUS_QUEUED;
	}

	public function __toString() {
		return '<Vote:' . $this->id . '>';
	}

}
