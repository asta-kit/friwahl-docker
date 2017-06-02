<?php
namespace AstaKit\FriWahl\Core\Domain\Aspect;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.Core".  *
 *                                                                        *
 *                                                                        */

use AstaKit\FriWahl\Core\Domain\Model\BallotBox;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Log\SystemLoggerInterface;


/**
 * Logging aspect for ballot boxes.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 *
 * @Flow\Aspect
 */
class BallotBoxLoggingAspect {

	/**
	 * @var SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $log;

	/**
	 *
	 * @Flow\AfterReturning("method(AstaKit\FriWahl\Core\Domain\Model\BallotBox->emit())")
	 */
	public function afterEmitAdvice(JoinPointInterface $joinPoint) {
		/** @var BallotBox $ballotBox */
		$ballotBox = $joinPoint->getProxy();

		$this->log->log('Ballot box ' . $ballotBox->getIdentifier() . ' was emitted.');
	}

	/**
	 *
	 * @Flow\AfterReturning("method(AstaKit\FriWahl\Core\Domain\Model\BallotBox->returnBox())")
	 */
	public function afterReturnAdvice(JoinPointInterface $joinPoint) {
		/** @var BallotBox $ballotBox */
		$ballotBox = $joinPoint->getProxy();

		$this->log->log('Ballot box ' . $ballotBox->getIdentifier() . ' was returned.');
	}

}
