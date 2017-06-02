<?php
namespace AstaKit\FriWahl\Core\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;


/**
 * Checks the access of a voter to a voting. Several of these can be used on a voting, if one of them denies access,
 * access to the voting is denied.
 *
 * Because of the ambiguity of “voter” in this context, this class and its descendants are referred to as
 * “access voters” or "participation voters".
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
abstract class BaseVotingAccessVoter {

	/**
	 * Checks if this access voter may vote for the participation of the given voter.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	abstract public function canVote(EligibleVoter $voter, Voting $voting);

	/**
	 * Checks access of the given voter to the given voting.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	abstract public function mayParticipate(EligibleVoter $voter, Voting $voting);
}
