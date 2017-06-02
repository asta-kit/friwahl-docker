<?php
namespace AstaKit\FriWahl\Core\Tests\Functional\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\EligibleVoter;
use AstaKit\FriWahl\Core\Domain\Model\VoterDiscriminator;
use TYPO3\Flow\Error\Debugger;
use TYPO3\Flow\Tests\FunctionalTestCase;


/**
 * Functional test for the eligible voter class.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class EligibleVoterTest extends FunctionalTestCase {

	/**
	 * {@inheritDoc}
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * This is a technical test to make sure that a voter's discriminators are correctly read back from the database
	 * and also indexed correctly in the collection. This test is necessary because the support for Doctrine's
	 * "indexBy" feature is a bit unstable in Flow 2.x (more specifically, it is not implemented and has been patched
	 * manually).
	 *
	 * @see <http://forge.typo3.org/issues/44740>
	 * @test
	 */
	public function getDiscriminatorReturnsCorrectObjectAfterVoterHasBeenPersistedAndRestored() {
		$voter = new EligibleVoter('some', 'voter');
		$voter->addDiscriminator('foo', 'bar');

		$voterIdentifier = $this->persistenceManager->getIdentifierByObject($voter);

		$this->persistenceManager->add($voter);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var EligibleVoter $voter */
		$voter = $this->persistenceManager->getObjectByIdentifier($voterIdentifier, 'AstaKit\FriWahl\Core\Domain\Model\EligibleVoter');

		$this->assertCount(1, $voter->getDiscriminators());
		$this->assertNotNull($voter->getDiscriminator('foo'));
		$this->assertEquals('bar', $voter->getDiscriminator('foo')->getValue());
	}
}
