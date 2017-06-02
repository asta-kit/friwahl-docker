<?php
namespace AstaKit\FriWahl\AdminInterface\Command;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Security\Account;
use TYPO3\Flow\Security\AccountFactory;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Party\Domain\Model\Person;
use TYPO3\Party\Domain\Model\PersonName;


/**
 * Command-line controller for user administration.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class UserCommandController extends CommandController {

	/**
	 * @var AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * Creates a user with the given names and password. Users are made member of the election committee.
	 *
	 * @param string $firstName The first name of the user
	 * @param string $lastName The last name of the user
	 * @param string $username The username used to log into the system
	 * @param string $password The password used for login.
	 */
	public function createCommand($firstName, $lastName, $username, $password) {
		$account = $this->createUserAccount($username, $password, array('AstaKit.FriWahl.AdminInterface:ElectionCommittee'));

		$user = $this->createUser($firstName, $lastName, $account);

		$this->persistenceManager->add($account);
		$this->persistenceManager->add($user);
	}

	/**
	 * Creates a user account
	 *
	 * @param string $username
	 * @param string $password
	 * @param array $roles The roles
	 * @return Account
	 */
	protected function createUserAccount($username, $password, $roles) {
		$account = $this->accountFactory->createAccountWithPassword($username, $password, $roles);

		return $account;
	}

	/**
	 * @param string $firstName
	 * @param string $lastName
	 * @param Account $account
	 * @return Person
	 */
	protected function createUser($firstName, $lastName, $account) {
		$party = new Person();
		$party->setName(new PersonName('', $firstName, '', $lastName));
		$party->addAccount($account);

		return $party;
	}
}
