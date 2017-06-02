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
 * A plebiscite.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class Plebiscite extends Voting {

	/**
	 * @var Collection<PlebisciteQuestion>
	 * @ORM\OneToMany(mappedBy="plebiscite")
	 */
	protected $questions;


	/**
	 * @return Plebiscite
	 */
	public function __construct($name, Election $election = NULL, VotingGroup $votingGroup = NULL) {
		parent::__construct($name, $election, $votingGroup);
		$this->questions = new ArrayCollection();
	}

	/**
	 * Returns the type of this record.
	 *
	 * @return string
	 */
	public function getType() {
		return 'Plebiscite';
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getQuestions() {
		return $this->questions;
	}

	/**
	 * @param PlebisciteQuestion $question
	 * @return void
	 */
	public function addQuestion(PlebisciteQuestion $question) {
		$this->questions->add($question);
	}
}
