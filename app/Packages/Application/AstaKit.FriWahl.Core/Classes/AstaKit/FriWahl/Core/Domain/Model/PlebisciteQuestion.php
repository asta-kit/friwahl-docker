<?php
namespace AstaKit\FriWahl\Core\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * A question and the possible answers for a plebiscite.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Entity
 */
class PlebisciteQuestion {

	/**
	 * @var Plebiscite
	 * @ORM\ManyToOne(inversedBy="questions")
	 */
	protected $plebiscite;

	/**
	 * @var string
	 */
	protected $question;

	/**
	 * @var array
	 */
	protected $optionAnswers;

	/**
	 * @param Plebiscite $plebiscite
	 */
	public function __construct(Plebiscite $plebiscite) {
		$this->plebiscite = $plebiscite;

		$plebiscite->addQuestion($this);
	}
}
