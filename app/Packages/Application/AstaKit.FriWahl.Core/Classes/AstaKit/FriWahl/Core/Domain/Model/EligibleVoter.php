<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A person who is able to vote in an election. The right to vote might be limited to certain votings, or a person
 * might be excluded from a voting for several reasons.
 *
 * In addition to the name, several configurable discriminators can be stored with a voter. These can be used to record
 * e.g. the group of voters a person belongs to, the matriculation number, the department where they can vote, or
 * their sex or nationality (to realize sex/nationality based votings for minority representations).
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class EligibleVoter {

	/**
	 * The voter's given name.
	 *
	 * @var string
	 * @ORM\Column(length=40)
	 */
	protected $givenName;

	/**
	 * The voter's family name.
	 *
	 * @var string
	 * @ORM\Column(length=40)
	 */
	protected $familyName;

	/**
	 * Special properties of this user, e.g. the matriculation number, sex, nationality, field of study or department.
	 *
	 * @var Collection<\AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator>
	 * @ORM\OneToMany(mappedBy="voter", indexBy="identifier", cascade={"persist", "remove"})
	 */
	protected $discriminators;

	/**
	 * The election this voter belongs to.
	 *
	 * @var Election
	 * @ORM\ManyToOne(inversedBy="voter")
	 */
	protected $election;

	/**
	 * The votes this voter has cast. This collection is read-only in this object, as new objects can only be created
	 * by a stored procedure.
	 *
	 * @var Collection<Vote>
	 * @ORM\OneToMany(mappedBy="voter", cascade={}, orphanRemoval=false)
	 */
	protected $votes;


	/**
	 * @param Election $election
	 * @param string $givenName
	 * @param string $familyName
	 */
	public function __construct($election, $givenName, $familyName) {
		$this->givenName = $givenName;
		$this->familyName = $familyName;

		$this->discriminators = new ArrayCollection();
		$this->votes = new ArrayCollection();

		$this->election = $election;
	}

	/**
	 * @return string
	 */
	public function getGivenName() {
		return $this->givenName;
	}

	/**
	 * @return string
	 */
	public function getFamilyName() {
		return $this->familyName;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->givenName . ' ' . $this->familyName;
	}

	/**
	 * Returns the election-unique identifier for this voter.
	 *
	 * @return string
	 */
	public function getIdentifier() {
		$letters = mb_substr($this->getGivenName(), 0, 1, 'UTF-8')
			. mb_substr($this->getFamilyName(), -1, 1, 'UTF-8');

		$letters = strtr($letters,
		array(
			'Ä' => 'A',
			'ä' => 'a',
			'Á' => 'A',
			'á' => 'a',
			'À' => 'A',
			'à' => 'a',
			'Ö' => 'O',
			'ö' => 'o',
			'Ó' => 'O',
			'ó' => 'o',
			'Ò' => 'O',
			'ò' => 'o',
			'Ü' => 'u',
			'ü' => 'u',
			'ß' => 's',
			'Ç' => 'C',
			'ç' => 'c',
			'È' => 'E',
			'è' => 'e',
			'É' => 'E',
			'é' => 'e',
			'Ø' => 'O',
			'ø' => 'o',
			'Æ' => 'A',
			'æ' => 'a',
		));

		$letters = strtoupper($letters);

		return $this->getDiscriminator('matriculationNumber')->getValue() . $letters;
	}

	/**
	 * Returns the election this voter belongs to.
	 *
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		return $this->election;
	}

	/**
	 * Adds a discriminator for this voter.
	 *
	 * @param string $identifier
	 * @param string $value
	 */
	public function addDiscriminator($identifier, $value) {
		$discriminator = new VoterDiscriminator($this, $identifier, $value);

		$this->discriminators->set($identifier, $discriminator);
	}

	/**
	 * Returns the discriminator with the given identifier, if present.
	 *
	 * @param string $identifier
	 * @return VoterDiscriminator|null
	 */
	public function getDiscriminator($identifier) {
		/**
		 * We use the Doctrine feature of using a property of the object as the key for the collection (indexBy column
		 * property).
		 * However, this currently (2014-05) is not supported natively by Flow and requires a custom patch. See
		 * http://forge.typo3.org/issues/44740 for its current status.
		 *
		 * See the test getDiscriminatorReturnsCorrectObjectAfterVoterHasBeenPersistedAndRestored()
		 */
		return $this->discriminators->get($identifier);
	}

	/**
	 * Checks if this voter has a discriminator with the given identifier.
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function hasDiscriminator($identifier) {
		return $this->getDiscriminator($identifier) !== NULL;
	}

	/**
	 * The discriminators for this voter.
	 *
	 * @return \Doctrine\Common\Collections\Collection<Discriminator>
	 */
	public function getDiscriminators() {
		return $this->discriminators;
	}

	/**
	 * Returns the list of votings this voter might participate in.
	 *
	 * @return Voting[]
	 */
	public function getVotings() {
		$allowedVotings = array();

		/** @var Voting $voting */
		foreach ($this->election->getVotings() as $voting) {
			if ($voting->isAllowedToParticipate($this)) {
				$allowedVotings[] = $voting;
			}
		}
		return $allowedVotings;
	}

	/**
	 * Returns the votes this voter has cast.
	 *
	 * This is manually updated by the voting service when creating new votes;
	 *
	 * @return Vote[]
	 */
	public function getVotes() {
		return $this->votes;
	}

	/**
	 * Adds a new vote for this voter.
	 *
	 * This is a strictly internal method only used by the VotingService.
	 * NEVER add a new vote object through this method.
	 *
	 * @param Vote $vote
	 * @return void
	 */
	public function addVote(Vote $vote) {
		$this->votes[] = $vote;
	}

	/**
	 * Removes pending votes from the voter object.
	 *
	 * This is a strictly internal method only used by the VotingService.
	 * NEVER call this manually unless you know what you're doing!
	 *
	 * @return int The number of removed vote objects
	 */
	public function removePendingVotes() {
		$removedVotes = 0;
		foreach ($this->votes as $vote) {
			/** @var $vote Vote */
			if ($vote->isQueued()) {
				$this->votes->removeElement($vote);
				++$removedVotes;
			}
		}
	}

}
