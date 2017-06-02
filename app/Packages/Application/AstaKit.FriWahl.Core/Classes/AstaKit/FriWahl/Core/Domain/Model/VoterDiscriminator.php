<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * An attribute to tell (groups of) voters apart. This could e.g. be the matriculation number, department membership,
 * sex or nationality.
 *
 * The name for this class has been chosen following the naming of the discriminator column/map in Doctrine, which
 * is used to tell different types of records apart in a single-table inheritance table.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="voter_identifier", columns={"voter", "identifier"})})
 */
class VoterDiscriminator {

	/**
	 * The voter this discriminator belongs to.
	 *
	 * @var EligibleVoter
	 * @ORM\ManyToOne(inversedBy="discriminators")
	 */
	protected $voter;

	/**
	 * The identifier for this discriminator
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * The value of this discriminator
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * @param EligibleVoter $voter
	 * @param string $identifier
	 * @param string $value
	 */
	public function __construct($voter, $identifier, $value) {
		$this->voter = $voter;
		$this->identifier = $identifier;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

}
