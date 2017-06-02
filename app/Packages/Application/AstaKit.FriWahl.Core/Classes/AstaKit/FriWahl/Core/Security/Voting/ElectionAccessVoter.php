<?php
namespace AstaKit\FriWahl\Core\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;


/**
 * Checks if a voter belongs to the voting's election, i.e. if they are entitled for the current voting at all.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ElectionAccessVoter extends BaseVotingAccessVoter {
	/**
	 * Checks if this access voter may vote for the participation of the given voter.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	public function canVote(EligibleVoter $voter, Voting $voting) {
		// This access voter can always vote because every voter belongs to an election
		return TRUE;
	}

	/**
	 * Checks access of the given voter to the given voting.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	public function mayParticipate(EligibleVoter $voter, Voting $voting) {
		return $voter->getElection() === $voting->getElection();
	}

}
