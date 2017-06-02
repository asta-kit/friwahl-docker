<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;


use AstaKit\FriWahl\Core\Domain\Model\Election;
use AstaKit\FriWahl\Core\Domain\Repository\ElectionRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryResultInterface;


/**
 * Model for a search within the voter directory. This class is not persisted in the database, but just used
 * to temporarily transfer parameters.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class VoterSearch {

	/**
	 * The search criteria
	 *
	 * @var array
	 */
	protected $criteria;

	/**
	 * @var Election
	 */
	protected $election;

	/**
	 * The search results; is filled when the search is really performed.
	 *
	 * @var QueryResultInterface
	 */
	protected $results;

	/**
	 * @var ElectionRepository
	 * @Flow\Inject
	 */
	protected $electionRepository;


	/**
	 * @param Election $election
	 * @param array $criteria
	 */
	public function __construct(Election $election, array $criteria) {
		$this->election = $election;
		$this->criteria = $criteria;
	}

	/**
	 * @return \AstaKit\FriWahl\Core\Domain\Model\Election
	 */
	public function getElection() {
		return $this->election;
	}

	/**
	 * @return array
	 */
	public function getCriteria() {
		return $this->criteria;
	}

	/**
	 * @return void
	 */
	public function executeSearch() {
		if ($this->results !== NULL) {
			return;
		}

		$this->results = $this->electionRepository->findVotersByCriteria($this->election, $this->criteria);
	}

	/**
	 * @return QueryResultInterface
	 */
	public function getResults() {
		if ($this->results === NULL) {
			$this->executeSearch();
		}

		return $this->results;
	}

}
 