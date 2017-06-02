<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */


/**
 * Turnout (participation) for a voting.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingTurnout {

	/**
	 * @var Voting
	 */
	protected $voting;

	/**
	 * @var array
	 */
	protected $results;

	public function __construct(Voting $voting, $results) {
		$this->voting = $voting;
		$this->results = $results;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Voting
	 */
	public function getVoting() {
		return $this->voting;
	}

	/**
	 * @return int
	 */
	public function getTotalVoterCount() {
		return $this->results['_all']['voters'];
	}

	/**
	 * @return int
	 */
	public function getTotalVotesCount() {
		return $this->results['_all']['votes'];
	}

	/**
	 * @return double
	 */
	public function getTotalTurnout() {
		return $this->getTotalVotesCount() / $this->getTotalVoterCount();
	}

	/**
	 * @return double
	 */
	public function getTotalTurnoutPercent() {
		return $this->getTotalTurnout() * 100;
	}

	/**
	 * @return bool
	 */
	public function hasSubResults() {
		return count($this->results) > 1;
	}

	/**
	 * Returns the sub-results for 
	 *
	 * @return array
	 * @throws \RuntimeException
	 */
	public function getSubResults() {
		if (!($this->voting instanceof VotingGroup)) {
			throw new \RuntimeException('Sub results are only supported for voting groups');
		}

		/** @var VotingGroup $voting */
		$votingGroup = $this->voting;
		$subResults = array();

		foreach ($votingGroup->getVotings() as $voting) {
			$subKeys = $voting->getDiscriminatorValues();

			$votesCount = $votersCount = 0;
			foreach ($subKeys as $key) {
				if ($key === '_all') {
					continue;
				}

				$votesCount += $this->results[$key]['votes'];
				$votersCount += $this->results[$key]['voters'];
			}
			$subResults[$voting->getName()] = new VotingTurnout($voting,
				array('_all' => array(
					'votes' => $votesCount,
					'voters' => $votersCount,
				))
			);
		}

		ksort($subResults);

		return $subResults;
	}

}
