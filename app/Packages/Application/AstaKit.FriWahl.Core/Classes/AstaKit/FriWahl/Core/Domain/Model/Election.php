<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use AstaKit\FriWahl\Core\Environment\SystemEnvironment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Model for an election.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class Election implements VotingsContainer {

	/**
	 * The name of this election.
	 *
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 * @ORM\Column(unique=true)
	 */
	protected $name;

	/**
	 * An internal identifier for this election; used to e.g. name it for command line scripts. Keep this short and
	 * simple to save typing on the command line ;-)
	 *
	 * We use this as the identity column as it has to be unique and should not be changed afterwards.
	 *
	 * @var string
	 * NOTE: both the Identity and Id annotations are required because Doctrine needs Id to make the column the primary
	 *       key and use it as the internal identifier. The support for Identity in Flow is probably a bit broken
	 *       currently, as stated by Christian Müller at <http://www.typo3.net/forum/thematik/zeige/thema/115172/>
	 * @Flow\Identity
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 */
	protected $identifier;

	/**
	 * The date this election was created.
	 *
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * The periods during which this election is active, i.e. voting is possible.
	 *
	 * @var Collection<ElectionPeriod>
	 * @ORM\OneToMany(mappedBy="election", cascade={"remove", "persist"})
	 */
	protected $periods = array();

	/**
	 * @var Collection<BallotBox>
	 * @ORM\OneToMany(mappedBy="election")
	 */
	protected $ballotBoxes = array();

	/**
	 * If this is set, this election is treated as a test vote and can be used for the automated system tests.
	 * Otherwise most tests will
	 *
	 * @var bool
	 */
	protected $test = FALSE;

	/**
	 * The votings in this election.
	 *
	 * @var Collection<Voting>
	 * @ORM\OneToMany(mappedBy="election")
	 */
	protected $votings;

	/**
	 * @var SystemEnvironment
	 * @Flow\Inject
	 */
	protected $systemEnvironment;

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;


	/**
	 * @param string $name The name of this election.
	 * @param string $identifier The name of the identifier.
	 */
	public function __construct($name, $identifier) {
		$this->name = $name;
		$this->created = new \DateTime();
		$this->identifier = $identifier;

		$this->periods     = new ArrayCollection();
		$this->ballotBoxes = new ArrayCollection();
		$this->votings     = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param array $periods
	 */
	public function setPeriods($periods) {
		$this->periods = $periods;
	}

	/**
	 * Adds a period to this election.
	 *
	 * @param ElectionPeriod $period
	 */
	public function addPeriod(ElectionPeriod $period) {
		$this->periods->add($period);
	}

	/**
	 * @return array
	 */
	public function getPeriods() {
		return $this->periods;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $ballotBoxes
	 */
	public function setBallotBoxes($ballotBoxes) {
		$this->ballotBoxes = $ballotBoxes;
	}

	/**
	 * Adds a ballot box to this election.
	 *
	 * @param BallotBox $ballotBox
	 */
	public function addBallotBox(BallotBox $ballotBox) {
		$this->ballotBoxes->add($ballotBox);
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getBallotBoxes() {
		return $this->ballotBoxes;
	}

	/**
	 * @param string $ballotBoxIdentifier
	 * @return bool
	 */
	public function hasBallotBox($ballotBoxIdentifier) {
		/** @var $ballotBox BallotBox */
		foreach ($this->ballotBoxes as $ballotBox) {
			if ($ballotBox->getIdentifier() === $ballotBoxIdentifier) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param boolean $test
	 */
	public function setTest($test) {
		$this->test = $test;
	}

	/**
	 * Returns TRUE if this election can be used as a test election, e.g. for manual or automated system tests.
	 *
	 * @return boolean
	 */
	public function isTest() {
		return $this->test;
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		$currentDate = $this->systemEnvironment->getCurrentDate();

		/** @var ElectionPeriod $period */
		foreach ($this->periods as $period) {
			if ($period->includes($currentDate)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	public function getVoterCount() {
		return $this->electionRepository->countVotersByElection($this);
	}

	/**
	 * Adds a voting to this election.
	 *
	 * @param Voting $voting
	 */
	public function addVoting(Voting $voting) {
		$this->votings->add($voting);
	}

	/**
	 * Returns all votings for this election.
	 *
	 * @return Collection<Voting>
	 */
	public function getVotings() {
		return $this->votings;
	}

}
