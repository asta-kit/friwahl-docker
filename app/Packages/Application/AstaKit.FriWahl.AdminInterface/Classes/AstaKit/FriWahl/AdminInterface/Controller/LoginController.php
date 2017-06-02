<?php
namespace AstaKit\FriWahl\AdminInterface\Controller;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.AdminInterface".  *
 *                                                                                  *
 *                                                                                  */

use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;


/**
 * Created by PhpStorm.
 * User: andreas.wolf
 * Date: 30.04.14
 * Time: 11:17
 */
class LoginController extends AbstractAuthenticationController {

	public function indexAction() {
	}

	/**
	 * Is called if authentication was successful. If there has been an
	 * intercepted request due to security restrictions, you might want to use
	 * something like the following code to restart the originally intercepted
	 * request:
	 *
	 * if ($originalRequest !== NULL) {
	 *     $this->redirectToRequest($originalRequest);
	 * }
	 * $this->redirect('someDefaultActionAfterLogin');
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		$this->redirect('index', 'Standard');
	}

	public function logoutAction() {
		parent::logoutAction();

		$this->redirect('index');
	}

}
 