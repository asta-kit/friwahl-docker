<?php
namespace AstaKit\FriWahl\Core\Security\Voting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\Voting;
use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\Logger;
use TYPO3\Flow\Log\SystemLoggerInterface;


/**
 * Access manager for votings; keeps track of all available access voters and asks each of them for a decision on
 * the right to vote of a voter for a given voting.
 *
 * The concept of using an access manager to handle the process of deciding on the access of a voter to a voting is
 * borrowed from Flow's security framework, where the AccessDecisionVoterManager does the same, just with a more
 * elaborate decision scheme (voters can abstain from voting, while we enforce a decision).
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VotingAccessManager {

	/**
	 * @var BaseVotingAccessVoter[]
	 */
	protected $accessVoters;

	/**
	 * @var SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $log;

	public function __construct() {
		$this->accessVoters[] = new VotingDiscriminatorAccessVoter();
		$this->accessVoters[] = new ElectionAccessVoter();
	}

	public function setAccessVoters(array $accessVoters) {
		$this->accessVoters = $accessVoters;
	}

	/**
	 * Checks the given voter/voting combination against all registered access voters. If any access voter returns a
	 * deny vote, access to the voting is denied, and it also is denied if no voter votes at all.
	 *
	 * @param EligibleVoter $voter
	 * @param Voting $voting
	 * @return bool
	 */
	public function mayParticipate(EligibleVoter $voter, Voting $voting) {
		$result = FALSE;
		foreach ($this->accessVoters as $accessVoter) {
			if (!$accessVoter->canVote($voter, $voting)) {
				continue;
			}

			$result = $accessVoter->mayParticipate($voter, $voting);
			if ($result === FALSE) {
				$this->log->log(sprintf("Access denied to voting %s for voter %s because of access voter %s",
						$voting->getName(),
						$voter->getName(),
						get_class($accessVoter)
					), LOG_DEBUG
				);
				// skip the next voters – access is denied if any of the voters denies it
				return $result;
			}
		}

		return $result;
	}
}
 