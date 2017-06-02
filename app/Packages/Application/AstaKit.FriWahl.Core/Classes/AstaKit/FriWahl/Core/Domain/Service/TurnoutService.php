<?php
namespace AstaKit\FriWahl\Core\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\VotingTurnout;
use AstaKit\FriWahl\Core\Domain\Repository\EligibleVoterRepository;
use AstaKit\FriWahl\Core\Domain\Repository\VoteRepository;
use TYPO3\Flow\Annotations as Flow;


/**
 * Service for getting the voting participation.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class TurnoutService {

	/**
	 * @var VoteRepository
	 * @Flow\Inject
	 */
	protected $voteRepository;

	/**
	 * @var EligibleVoterRepository
	 * @Flow\Inject
	 */
	protected $voterRepository;


	/**
	 * @param Voting $voting
	 */
	public function getTurnoutForVoting(Voting $voting) {
		$turnout = array(
			'_all' => array(
				'votes' => 0,
				'voters' => 0,
			)
		);

		if ($voting->getDiscriminator() !== '') {
			// participation is limited to certain discriminator values – we get a result per discriminator value
			$castedVotes = $this->voteRepository->countByDiscriminatorValuesForVoting($voting);
			$voterCounts = $this->voterRepository->countByDiscriminatorsValues($voting->getElection(), $voting->getDiscriminator());

			// TODO this currently does not support discrimination mode "DENY"
			$relevantDiscriminatorValues = $voting->getDiscriminatorValues();

			$this->fillTurnoutArrayWithZeroes($relevantDiscriminatorValues, $turnout);
			$this->mergeVoteCountsToTurnout($castedVotes, $turnout);
			$this->mergeVoterCountsToTurnout($voterCounts, $turnout);
		} else {
			// everybody may participate – we get a single result
			$castedVotes = $this->voteRepository->countByVoting($voting);
			$voterCount = $this->voterRepository->countByElection($voting->getElection());

			$turnout['_all']['votes'] = $castedVotes;
			$turnout['_all']['voters'] = $voterCount;
		}

		$turnout = new VotingTurnout($voting, $turnout);
		return $turnout;
	}

	/**
	 * @param array $relevantDiscriminatorValues
	 * @param array $turnout
	 * @return mixed
	 */
	private function fillTurnoutArrayWithZeroes($relevantDiscriminatorValues, &$turnout) {
		foreach ($relevantDiscriminatorValues as $discriminator) {
			$turnout[$discriminator] = array(
				'votes' => 0,
				'voters' => 0,
			);
		}
	}

	/**
	 * Merges the vote counts returned by the vote repository to the turnout array.
	 *
	 * @param array $castedVotes
	 * @param array $turnout
	 * @return int The total number of votes
	 */
	private function mergeVoteCountsToTurnout($castedVotes, &$turnout) {
		$totalVoteCount = 0;

		foreach ($castedVotes as $votes) {
			$discriminator = $votes['discriminator'];
			if (!in_array($discriminator, array_keys($turnout))) {
				continue;
			}

			$turnout[$discriminator]['votes'] = $votes['cnt'];
			$totalVoteCount += $votes['cnt'];
		}

		$turnout['_all']['votes'] = $totalVoteCount;
	}

	private function mergeVoterCountsToTurnout($voterCounts, &$turnout) {
		$totalVoterCount = 0;

		foreach ($voterCounts as $votes) {
			$discriminator = $votes['discriminator'];
			if (!in_array($discriminator, array_keys($turnout))) {
				continue;
			}

			$turnout[$discriminator]['voters'] = $votes['cnt'];
			$totalVoterCount += $votes['cnt'];
		}

		$turnout['_all']['voters'] = $totalVoterCount;
	}

}
 