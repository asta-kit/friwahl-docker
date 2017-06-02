<?php 
namespace TYPO3\Flow\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to security
 *
 * @Flow\Scope("singleton")
 */
class SecurityCommandController_Original extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServicePhp
	 * @Flow\Inject
	 */
	protected $rsaWalletService;

	/**
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

	/**
	 * @var \TYPO3\Flow\Cache\Frontend\VariableFrontend
	 */
	protected $policyCache;

	/**
	 * Injects the Cache Manager because we cannot inject an automatically factored cache during compile time.
	 *
	 * @param \TYPO3\Flow\Cache\CacheManager $cacheManager
	 * @return void
	 */
	public function injectCacheManager(\TYPO3\Flow\Cache\CacheManager $cacheManager) {
		$this->policyCache = $cacheManager->getCache('Flow_Security_Policy');
	}

	/**
	 * Import a public key
	 *
	 * Read a PEM formatted public key from stdin and import it into the
	 * RSAWalletService.
	 *
	 * @return void
	 * @see typo3.flow:security:importprivatekey
	 */
	public function importPublicKeyCommand() {
		$keyData = '';
		// no file_get_contents here because it does not work on php://stdin
		$fp = fopen('php://stdin', 'rb');
		while (!feof($fp)) {
			$keyData .= fgets($fp, 4096);
		}
		fclose($fp);

		$uuid = $this->rsaWalletService->registerPublicKeyFromString($keyData);

		$this->outputLine('The public key has been successfully imported. Use the following uuid to refer to it in the RSAWalletService: ' . PHP_EOL . PHP_EOL . $uuid . PHP_EOL);
	}

	/**
	 * Import a private key
	 *
	 * Read a PEM formatted private key from stdin and import it into the
	 * RSAWalletService. The public key will be automatically extracted and stored
	 * together with the private key as a key pair.
	 *
	 * @param boolean $usedForPasswords If the private key should be used for passwords
	 * @return void
	 * @see typo3.flow:security:importpublickey
	 */
	public function importPrivateKeyCommand($usedForPasswords = FALSE) {
		$keyData = '';
		// no file_get_contents here because it does not work on php://stdin
		$fp = fopen('php://stdin', 'rb');
		while (!feof($fp)) {
			$keyData .= fgets($fp, 4096);
		}
		fclose($fp);

		$uuid = $this->rsaWalletService->registerKeyPairFromPrivateKeyString($keyData, $usedForPasswords);

		$this->outputLine('The keypair has been successfully imported. Use the following uuid to refer to it in the RSAWalletService: ' . PHP_EOL . PHP_EOL . $uuid . PHP_EOL);
	}

	/**
	 * Shows the effective policy rules currently active in the system
	 *
	 * @param boolean $grantsOnly Only list methods effectively granted to the given roles
	 * @return void
	 */
	public function showEffectivePolicyCommand($grantsOnly = FALSE) {
		$roles = array();
		$roleIdentifiers = $this->request->getExceedingArguments();

		if (empty($roleIdentifiers) === TRUE) {
			$this->outputLine('Please specify at leas one role, to calculate the effective privileges for!');
			$this->quit(1);
		}

		foreach ($roleIdentifiers as $roleIdentifier) {
			if ($this->policyService->hasRole($roleIdentifier)) {
				$currentRole = $this->policyService->getRole($roleIdentifier);
				$roles[$roleIdentifier] = $currentRole;
				foreach ($this->policyService->getAllParentRoles($currentRole) as $parentRoleIdentifier => $parentRole) {
					if (!isset($roles[$parentRoleIdentifier])) {
						$roles[$parentRoleIdentifier] = $parentRole;
					}
				}
			}
		}

		if (count($roles) === 0) {
			$this->outputLine('The specified role(s) do not exist.');
			$this->quit(1);
		}

		$this->outputLine(PHP_EOL . 'The following roles will be used for calculating the effective privileges (retrieved from the configured roles hierarchy):' . PHP_EOL);
		foreach($roles as $roleIdentifier => $role) {
			$this->outputLine($roleIdentifier);
		}

		$dummySecurityContext = new \TYPO3\Flow\Security\DummyContext();
		$dummySecurityContext->setRoles($roles);
		$accessDecisionManager = new \TYPO3\Flow\Security\Authorization\AccessDecisionVoterManager($this->objectManager, $dummySecurityContext);

		if ($this->policyCache->has('acls')) {
			$classes = array();
			$acls = $this->policyCache->get('acls');
			foreach($acls as $classAndMethodName => $aclEntry) {
				if (strpos($classAndMethodName, '->') === FALSE) {
					continue;
				}
				list($className, $methodName) = explode('->', $classAndMethodName);
				$className = $this->objectManager->getCaseSensitiveObjectName($className);
				$reflectionClass = new \ReflectionClass($className);
				foreach ($reflectionClass->getMethods() as $casSensitiveMethodName) {
					if ($methodName === strtolower($casSensitiveMethodName->getName())) {
						$methodName = $casSensitiveMethodName->getName();
						break;
					}
				}
				$runtimeEvaluationsInPlace = FALSE;
				foreach($aclEntry as $role => $resources) {
					if (in_array($role, $roles) === FALSE) {
						continue;
					}

					if (!isset($classes[$className])) {
						$classes[$className] = array();
					}
					if (!isset($classes[$className][$methodName])) {
						$classes[$className][$methodName] = array();
						$classes[$className][$methodName]['resources'] = array();
					}

					foreach($resources as $resourceName => $privilege) {
						$classes[$className][$methodName]['resources'][$resourceName] = $privilege;
						if ($privilege['runtimeEvaluationsClosureCode'] !== FALSE) {
							$runtimeEvaluationsInPlace = TRUE;
						}
					}
				}

				if ($runtimeEvaluationsInPlace === FALSE) {
					try {
						$accessDecisionManager->decideOnJoinPoint(new \TYPO3\Flow\Aop\JoinPoint(NULL, $className, $methodName, array()));
					} catch (\TYPO3\Flow\Security\Exception\AccessDeniedException $e) {
						$classes[$className][$methodName]['effectivePrivilege'] = $e->getMessage();
					}
					if (!isset($classes[$className][$methodName]['effectivePrivilege'])) {
						$classes[$className][$methodName]['effectivePrivilege'] = 'Access granted';
					}
				} else {
					$classes[$className][$methodName]['effectivePrivilege'] = 'Could not be calculated. Runtime evaluations in place!';
				}
			}

			foreach ($classes as $className => $methods) {
				$classNamePrinted = FALSE;
				foreach ($methods as $methodName => $resources) {
					if ($grantsOnly === TRUE && $resources['effectivePrivilege'] !== 'Access granted') {
						continue;
					}
					if ($classNamePrinted === FALSE) {
						$this->outputLine(PHP_EOL . PHP_EOL . ' <b>' . $className . '</b>');
						$classNamePrinted = TRUE;
					}

					$this->outputLine(PHP_EOL . '  ' . $methodName);
					if (isset($resources['resources']) === TRUE && is_array($resources['resources']) === TRUE) {
						foreach ($resources['resources'] as $resourceName => $privilege) {
							switch ($privilege['privilege']) {
								case \TYPO3\Flow\Security\Policy\PolicyService::PRIVILEGE_GRANT:
									$this->outputLine('   Resource "<i>' . $resourceName . '</i>": Access granted');
									break;
								case \TYPO3\Flow\Security\Policy\PolicyService::PRIVILEGE_DENY:
									$this->outputLine('   Resource "<i>' . $resourceName . '</i>": Access denied');
									break;
								case \TYPO3\Flow\Security\Policy\PolicyService::PRIVILEGE_ABSTAIN:
									$this->outputLine('   Resource "<i>' . $resourceName . '</i>": Vote abstained (no acl entry for given roles)');
									break;
							}
						}
					}
					$this->outputLine('   <b>Effective privilege for given roles: ' . $resources['effectivePrivilege'] . '</b>');
				}
			}
		} else {
			$this->outputLine('Could not find any policy entries, please warmup caches...');
		}
	}

	/**
	 * Lists all public controller actions not covered by the active security policy
	 *
	 * @return void
	 */
	public function showUnprotectedActionsCommand() {
		$controllerClassNames = $this->reflectionService->getAllSubClassNamesForClass('TYPO3\Flow\Mvc\Controller\AbstractController');

		$allActionsAreProtected = TRUE;
		foreach ($controllerClassNames as $controllerClassName) {
			if ($this->reflectionService->isClassAbstract($controllerClassName)) {
				continue;
			}

			$methodNames = get_class_methods($controllerClassName);

			$foundUnprotectedAction = FALSE;
			foreach ($methodNames as $methodName) {
				if (preg_match('/.*Action$/', $methodName) === 0 || $this->reflectionService->isMethodPublic($controllerClassName, $methodName) === FALSE) {
					continue;
				}

				if ($this->policyService->hasPolicyEntryForMethod($controllerClassName, $methodName) === FALSE) {
					if ($foundUnprotectedAction === FALSE) {
						$this->outputLine(PHP_EOL . '<b>' . $controllerClassName . '</b>');
						$foundUnprotectedAction = TRUE;
						$allActionsAreProtected = FALSE;
					}
					$this->outputLine('  ' . $methodName);
				}
			}
		}

		if ($allActionsAreProtected === TRUE) {
			$this->outputLine('All public controller actions are covered by your security policy. Good job!');
		}
	}

	/**
	 * Shows the methods represented by the given security resource
	 *
	 * @param string $resourceName The name of the resource as stated in the policy
	 * @return void
	 */
	public function showMethodsForResourceCommand($resourceName) {
		if ($this->policyCache->has('acls')) {
			$classes = array();
			$acls = $this->policyCache->get('acls');
			foreach($acls as $classAndMethodName => $aclEntry) {
				if (strpos($classAndMethodName, '->') === FALSE) {
					continue;
				}
				list($className, $methodName) = explode('->', $classAndMethodName);
				$className = $this->objectManager->getCaseSensitiveObjectName($className);
				$reflectionClass = new \ReflectionClass($className);
				foreach ($reflectionClass->getMethods() as $casSensitiveMethodName) {
					if ($methodName === strtolower($casSensitiveMethodName->getName())) {
						$methodName = $casSensitiveMethodName->getName();
						break;
					}
				}
				foreach($aclEntry as $resources) {
					if (array_key_exists($resourceName, $resources)) {
						$classes[$className][$methodName] = $methodName;
						break;
					}
				}
			}

			if (count($classes) === 0) {
				$this->outputLine('The given Resource did not match any method or is unknown.');
				$this->quit(1);
			}

			foreach ($classes as $className => $methods) {
				$this->outputLine(PHP_EOL . $className);
				foreach ($methods as $methodName) {
					$this->outputLine('  ' . $methodName);
				}
			}
		} else {
			$this->outputLine('Could not find any policy entries, please warmup caches!');
		}
	}
}
namespace TYPO3\Flow\Command;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to security
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class SecurityCommandController extends SecurityCommandController_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'TYPO3\Flow\Command\SecurityCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Command\SecurityCommandController', $this);
		parent::__construct();
		if ('TYPO3\Flow\Command\SecurityCommandController' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'TYPO3\Flow\Command\SecurityCommandController') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Command\SecurityCommandController', $this);

	if (property_exists($this, 'Flow_Persistence_RelatedEntities') && is_array($this->Flow_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		foreach ($this->Flow_Persistence_RelatedEntities as $entityInformation) {
			$entity = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType'], TRUE);
			if (isset($entityInformation['entityPath'])) {
				$this->$entityInformation['propertyName'] = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$entityInformation['propertyName'], $entityInformation['entityPath'], $entity);
			} else {
				$this->$entityInformation['propertyName'] = $entity;
			}
		}
		unset($this->Flow_Persistence_RelatedEntities);
	}
				$this->Flow_Proxy_injectProperties();
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\Flow\Command\SecurityCommandController');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Flow\Command\SecurityCommandController', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
		if (is_array($this->$propertyName) || (is_object($this->$propertyName) && ($this->$propertyName instanceof \ArrayObject || $this->$propertyName instanceof \SplObjectStorage ||$this->$propertyName instanceof \Doctrine\Common\Collections\Collection))) {
			if (count($this->$propertyName) > 0) {
				foreach ($this->$propertyName as $key => $value) {
					$this->searchForEntitiesAndStoreIdentifierArray((string)$key, $value, $propertyName);
				}
			}
		}
		if (is_object($this->$propertyName) && !$this->$propertyName instanceof \Doctrine\Common\Collections\Collection) {
			if ($this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($this->$propertyName);
			} else {
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Flow\Command\SecurityCommandController', $propertyName, 'var');
				if (count($varTagValues) > 0) {
					$className = trim($varTagValues[0], '\\');
				}
				if (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->isRegistered($className) === FALSE) {
					$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($this->$propertyName));
				}
			}
			if ($this->$propertyName instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($this->$propertyName) || $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
					$this->Flow_Persistence_RelatedEntities = array();
					$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
				}
				$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($this->$propertyName);
				if (!$identifier && $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
					$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($this->$propertyName, '_identifier', TRUE));
				}
				$this->Flow_Persistence_RelatedEntities[$propertyName] = array(
					'propertyName' => $propertyName,
					'entityType' => $className,
					'identifier' => $identifier
				);
				continue;
			}
			if ($className !== FALSE && (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getScope($className) === \TYPO3\Flow\Object\Configuration\Configuration::SCOPE_SINGLETON || $className === 'TYPO3\Flow\Object\DependencyInjection\DependencyProxy')) {
				continue;
			}
		}
		$this->Flow_Object_PropertiesToSerialize[] = $propertyName;
	}
	$result = $this->Flow_Object_PropertiesToSerialize;
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function searchForEntitiesAndStoreIdentifierArray($path, $propertyValue, $originalPropertyName) {

		if (is_array($propertyValue) || (is_object($propertyValue) && ($propertyValue instanceof \ArrayObject || $propertyValue instanceof \SplObjectStorage))) {
			foreach ($propertyValue as $key => $value) {
				$this->searchForEntitiesAndStoreIdentifierArray($path . '.' . $key, $value, $originalPropertyName);
			}
		} elseif ($propertyValue instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($propertyValue) || $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
			if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
				$this->Flow_Persistence_RelatedEntities = array();
				$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
			}
			if ($propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($propertyValue);
			} else {
				$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($propertyValue));
			}
			$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($propertyValue);
			if (!$identifier && $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($propertyValue, '_identifier', TRUE));
			}
			$this->Flow_Persistence_RelatedEntities[$originalPropertyName . '.' . $path] = array(
				'propertyName' => $originalPropertyName,
				'entityType' => $className,
				'identifier' => $identifier,
				'entityPath' => $path
			);
			$this->$originalPropertyName = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$originalPropertyName, $path, NULL);
		}
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function Flow_Proxy_injectProperties() {
		$this->injectCacheManager(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Cache\CacheManager'));
		$this->injectReflectionService(\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService'));
		$objectManager_reference = &$this->objectManager;
		$this->objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Object\ObjectManagerInterface');
		if ($this->objectManager === NULL) {
			$this->objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('0c3c44be7be16f2a287f1fb2d068dde4', $objectManager_reference);
			if ($this->objectManager === NULL) {
				$this->objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('0c3c44be7be16f2a287f1fb2d068dde4',  $objectManager_reference, 'TYPO3\Flow\Object\ObjectManager', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Object\ObjectManagerInterface'); });
			}
		}
		$rsaWalletService_reference = &$this->rsaWalletService;
		$this->rsaWalletService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Security\Cryptography\RsaWalletServicePhp');
		if ($this->rsaWalletService === NULL) {
			$this->rsaWalletService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('47cd054dfc0feacf9b3f977ab72e0fd8', $rsaWalletService_reference);
			if ($this->rsaWalletService === NULL) {
				$this->rsaWalletService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('47cd054dfc0feacf9b3f977ab72e0fd8',  $rsaWalletService_reference, 'TYPO3\Flow\Security\Cryptography\RsaWalletServicePhp', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Security\Cryptography\RsaWalletServicePhp'); });
			}
		}
		$policyService_reference = &$this->policyService;
		$this->policyService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('TYPO3\Flow\Security\Policy\PolicyService');
		if ($this->policyService === NULL) {
			$this->policyService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('16231078e783810895dba92e364c25f7', $policyService_reference);
			if ($this->policyService === NULL) {
				$this->policyService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('16231078e783810895dba92e364c25f7',  $policyService_reference, 'TYPO3\Flow\Security\Policy\PolicyService', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Security\Policy\PolicyService'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'cacheManager',
  1 => 'reflectionService',
  2 => 'objectManager',
  3 => 'rsaWalletService',
  4 => 'policyService',
);
	}
}
#