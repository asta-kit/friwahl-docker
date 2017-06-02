<?php
namespace AstaKit\FriWahl\Core\Environment;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


/**
 * The system environment. This is used to abstract e.g. the current date and time to be able to simulate a different
 * date in tests.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Scope("singleton")
 */
class SystemEnvironment {

	/**
	 * The mocked datetime object
	 *
	 * @var \DateTime
	 */
	protected $mockedDate;

	/**
	 * @param \DateTime $mockedDate
	 */
	public function setMockedDate($mockedDate) {
		$this->mockedDate = $mockedDate;
	}

	/**
	 * Returns the current date and time.
	 *
	 * @return \DateTime
	 */
	public function getCurrentDate() {
		if (!$this->mockedDate) {
			return new \DateTime();
		}
		return $this->mockedDate;
	}
}
