<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * A group of votings that is made available to voters based on a discriminator. Each voter can access at maximum
 * one voting.
 *
 * This can be used to e.g. implement a voting based on departments with different lists for each department.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class VotingGroup extends Voting implements VotingsContainer {

	/**
	 * The votings that are part of this group.
	 *
	 * @var Collection<Voting>
	 * @ORM\OneToMany(mappedBy="votingGroup")
	 */
	protected $votings;

	/**
	 * @param string $name
	 * @param Election $election The election this voting belongs to
	 */
	public function __construct($name, Election $election) {
		parent::__construct($name, $election);

		$this->votings = new ArrayCollection();
	}


	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getVotings() {
		return $this->votings;
	}

	/**
	 * @param Voting $voting
	 * @return void
	 */
	public function addVoting(Voting $voting) {
		$this->votings->add($voting);
	}

	/**
	 * Returns the type of this record.
	 *
	 * @return string
	 */
	public function getType() {
		return 'VotingGroup';
	}

	public function getDiscriminatorValues() {
		$discriminatorValues = array();

		/** @var Voting $voting */
		foreach ($this->votings as $voting) {
			$discriminatorValues = array_merge($discriminatorValues, $voting->getDiscriminatorValues());
		}
		return array_unique($discriminatorValues);
	}

}
