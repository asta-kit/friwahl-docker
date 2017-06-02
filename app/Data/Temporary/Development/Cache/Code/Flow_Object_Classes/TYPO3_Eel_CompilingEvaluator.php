<?php 
namespace TYPO3\Eel;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Eel".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * An evaluator that compiles expressions down to PHP code
 *
 * This simple implementation will lazily parse and evaluate the generated PHP
 * code into a function with a name built from the hashed expression.
 *
 * @Flow\Scope("singleton")
 */
class CompilingEvaluator_Original implements EelEvaluatorInterface {

	/**
	 * @var array
	 */
	protected $newExpressions = array();

	/**
	 * @Flow\Inject(lazy=false)
	 * @var \TYPO3\Flow\Cache\Frontend\PhpFrontend
	 */
	protected $expressionCache;

	/**
	 * Initialize the Evaluator
	 */
	public function initializeObject() {
		$this->expressionCache->requireOnce('cachedExpressionClosures');
	}

	/**
	 * Shutdown the Evaluator
	 */
	public function shutdownObject() {
		if (count($this->newExpressions) > 0) {
			$changesToPersist = FALSE;
			$codeToBeCached = $this->expressionCache->get('cachedExpressionClosures');
			/**
			 * At this point a race condition could happen, that we try to prevent with an additional check.
			 * So we compare the evaluated expressions during this request with the methods the cache has at
			 * this point and only add methods that are not present. Only if we added anything we write the cache.
			 */
			foreach ($this->newExpressions as $functionName => $newExpression) {
				if (strpos($codeToBeCached, $functionName) === FALSE) {
					$codeToBeCached .= $newExpression . chr(10);
					$changesToPersist = TRUE;
				}
			}

			if ($changesToPersist) {
				$this->expressionCache->set('cachedExpressionClosures', $codeToBeCached);
			}
		}
	}

	/**
	 * Evaluate an expression under a given context
	 *
	 * @param string $expression
	 * @param Context $context
	 * @return mixed
	 */
	public function evaluate($expression, Context $context) {
		$identifier = md5($expression);
		$functionName = 'expression_' . $identifier;

		if (!function_exists($functionName)) {
			$code = $this->generateEvaluatorCode($expression);
			$functionDeclaration = 'function ' . $functionName . '($context){return ' . $code . ';}';
			$this->newExpressions[$functionName] = $functionDeclaration;
			eval($functionDeclaration);
		}

		$result = $functionName($context);
		if ($result instanceof Context) {
			return $result->unwrap();
		} else {
			return $result;
		}
	}

	/**
	 * Internal generator method
	 *
	 * Used by unit tests to debug generated PHP code.
	 *
	 * @param string $expression
	 * @return string
	 * @throws ParserException
	 */
	protected function generateEvaluatorCode($expression) {
		$parser = new CompilingEelParser($expression);
		$result = $parser->match_Expression();

		if ($result === FALSE) {
			throw new ParserException(sprintf('Expression "%s" could not be parsed.', $expression), 1344513194);
		} elseif ($parser->pos !== strlen($expression)) {
			throw new ParserException(sprintf('Expression "%s" could not be parsed. Error starting at character %d: "%s".', $expression, $parser->pos, substr($expression, $parser->pos)), 1327682383);
		} elseif (!array_key_exists('code', $result)) {
			throw new ParserException(sprintf('Parser error, no code in result %s ', json_encode($result)), 1334491498);
		}
		return $result['code'];
	}

}
namespace TYPO3\Eel;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * An evaluator that compiles expressions down to PHP code
 * 
 * This simple implementation will lazily parse and evaluate the generated PHP
 * code into a function with a name built from the hashed expression.
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class CompilingEvaluator extends CompilingEvaluator_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Eel\CompilingEvaluator', $this);
		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Eel\EelEvaluatorInterface', $this);
		if ('TYPO3\Eel\CompilingEvaluator' === get_class($this)) {
			$this->Flow_Proxy_injectProperties();
		}

		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') {
			$this->initializeObject(1);
		}

		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') {
		\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
		}
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Eel\CompilingEvaluator', $this);
		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Eel\EelEvaluatorInterface', $this);

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
		$result = NULL;

		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') {
			$this->initializeObject(2);
		}

		if (get_class($this) === 'TYPO3\Eel\CompilingEvaluator') {
		\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
		}
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\Eel\CompilingEvaluator');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Eel\CompilingEvaluator', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Eel\CompilingEvaluator', $propertyName, 'var');
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
		$expressionCache_reference = &$this->expressionCache;
		$this->expressionCache = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getInstance('');
		if ($this->expressionCache === NULL) {
			$this->expressionCache = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getLazyDependencyByHash('991863581f543cf0f9e83125c30b42c8', $expressionCache_reference);
			if ($this->expressionCache === NULL) {
				$this->expressionCache = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->createLazyDependency('991863581f543cf0f9e83125c30b42c8',  $expressionCache_reference, '', function() { return \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Cache\CacheManager')->getCache('Eel_Expression_Code'); });
			}
		}
$this->Flow_Injected_Properties = array (
  0 => 'expressionCache',
);
	}
}
#