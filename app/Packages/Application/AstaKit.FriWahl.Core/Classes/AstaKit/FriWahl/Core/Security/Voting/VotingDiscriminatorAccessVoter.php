<?php
namespace AstaKit\FriWahl\Core\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;


/**
 * Access voter for votings that are based on a discriminator. It will only vote if there is a discriminator defined
 * with the voting.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingDiscriminatorAccessVoter extends BaseVotingAccessVoter {

	/**
	 * Checks if this access voter may vote for the participation of the given voter.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	public function canVote(EligibleVoter $voter, Voting $voting) {
		// only let this voter decide on the voting if there is a discriminator
		return $voting->getDiscriminator() !== '';
	}

	/**
	 * Checks access of the given voter to the given voting.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	public function mayParticipate(EligibleVoter $voter, Voting $voting) {
		$discriminator = $voter->getDiscriminator($voting->getDiscriminator());
		if ($discriminator) {
			if ($voting->getDiscriminationMode() === Voting::DISCRIMINATION_MODE_ALLOW
				&& in_array($discriminator->getValue(), $voting->getDiscriminatorValues())
			) {
				return TRUE;
			}

			if ($voting->getDiscriminationMode() === Voting::DISCRIMINATION_MODE_DENY
				&& !in_array($discriminator->getValue(), $voting->getDiscriminatorValues())
			) {
				return TRUE;
			}
		}

		return FALSE;
	}

}
