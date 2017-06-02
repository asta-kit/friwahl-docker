<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ElectionPeriod {

	/**
	 * @var \DateTime
	 */
	protected $start;

	/**
	 * @var \DateTime
	 */
	protected $end;

	/**
	 * @var Election
	 * @ORM\ManyToOne
	 */
	protected $election;

	/**
	 * @param \DateTime $start
	 * @param \DateTime $end
	 * @param Election $election
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\DateTime $start, \DateTime $end, Election $election) {
		if ($start > $end) {
			throw new \InvalidArgumentException('Start date must be before end date');
		}

		$this->start = $start;
		$this->end = $end;
		$this->election = $election;
		// we're adding the period to the election here because a) the election will never change and b) the owning
		// side of the relation is here and not in Election (though this is only a technical implication from the way
		// Doctrine handles 1:n relations)
		$this->election->addPeriod($this);
	}

	/**
	 * Checks if the given date time is within the period. Start and end time are treated as part of the period.
	 *
	 * @param \DateTime $compare
	 * @return boolean
	 */
	public function includes(\DateTime $compare) {
		if ($compare < $this->start || $compare > $this->end) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		return $this->election;
	}

	/**
	 * @return \DateTime
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * @return \DateTime
	 */
	public function getEnd() {
		return $this->end;
	}

}