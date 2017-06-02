<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */


/**
 * Marker interface for objects that can contain a voting. This interface exists because we have two possibilities how
 * a voting can be used: either standing on its own, or as part of a voting group. For the latter case, there should
 * be no direct reference from the voting to the election.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
interface VotingsContainer {

	public function addVoting(Voting $voting);

	public function getVotings();

}
