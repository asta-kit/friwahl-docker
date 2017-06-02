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

/**
 * A Eel evaluation context
 *
 * It works as a variable container with wrapping of return values
 * for safe access without warnings (on missing properties).
 */
class Context_Original {

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param mixed $value
	 */
	public function __construct($value = NULL) {
		$this->value = $value;
	}

	/**
	 * Get a value of the context
	 *
	 * This basically acts as a safe access to non-existing properties, unified array and
	 * property access (using getters) and access to the current value (empty path).
	 *
	 * If a property or key did not exist this method will return NULL.
	 *
	 * @param string|integer|Context $path The path as string or Context value, will be unwrapped for convenience
	 * @return mixed The value
	 * @throws EvaluationException
	 */
	public function get($path) {
		if ($path instanceof Context) {
			$path = $path->unwrap();
		}
		if ($path === NULL) {
			return NULL;
		} elseif (is_string($path) || is_integer($path)) {
			if (is_array($this->value)) {
				return array_key_exists($path, $this->value) ? $this->value[$path] : NULL;
			} elseif (is_object($this->value)) {
				try {
					return \TYPO3\Flow\Reflection\ObjectAccess::getProperty($this->value, $path);
				} catch (\TYPO3\Flow\Reflection\Exception\PropertyNotAccessibleException $exception) {
					return NULL;
				}
			}
		} else {
			throw new EvaluationException('Path is not of type string or integer, got ' . gettype($path), 1344418464);
		}
	}

	/**
	 * Get a value by path and wrap it into another context
	 *
	 * @param string $path
	 * @return \TYPO3\Eel\Context The wrapped value
	 */
	public function getAndWrap($path = NULL) {
		return $this->wrap($this->get($path));
	}

	/**
	 * Call a method on this context
	 *
	 * @param string $method
	 * @param array $arguments Arguments to the method, if of type Context they will be unwrapped
	 * @return mixed
	 * @throws \Exception
	 */
	public function call($method, array $arguments = array()) {
		if ($this->value === NULL) {
			return NULL;
		} elseif (is_object($this->value)) {
			$callback = array($this->value, $method);
		} elseif (is_array($this->value)) {
			if (!array_key_exists($method, $this->value)) {
				throw new EvaluationException('Array has no function "' . $method . '"', 1344350459);
			}
			$callback = $this->value[$method];
		} else {
			throw new EvaluationException('Needs object or array to call method "' . $method . '", but has ' . gettype($this->value), 1344350454);
		}
		if (!is_callable($callback)) {
			throw new EvaluationException('Method "' . $method . '" is not callable', 1344350374);
		}
		for ($i = 0; $i < count($arguments); $i++) {
			if ($arguments[$i] instanceof Context) {
				$arguments[$i] = $arguments[$i]->unwrap();
			}
		}
		return call_user_func_array($callback, $arguments);
	}

	/**
	 * Call a method and wrap the result
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function callAndWrap($method, array $arguments = array()) {
		return $this->wrap($this->call($method, $arguments));
	}

	/**
	 * Wraps the given value in a new Context
	 *
	 * @param mixed $value
	 * @return \TYPO3\Eel\Context
	 */
	public function wrap($value) {
		if (!$value instanceof Context) {
			return new static($value);
		} else {
			return $value;
		}
	}

	/**
	 * Unwrap the context value recursively
	 *
	 * @return mixed
	 */
	public function unwrap() {
		return $this->unwrapValue($this->value);
	}

	/**
	 * Unwrap a value by unwrapping nested context objects
	 *
	 * This method is public for closure access.
	 *
	 * @param $value
	 * @return mixed
	 */
	public function unwrapValue($value) {
		if (is_array($value)) {
			$self = $this;
			return array_map(function($item) use ($self) {
				if ($item instanceof Context) {
					return $item->unwrap();
				} else {
					return $self->unwrapValue($item);
				}
			}, $value);
		} else {
			return $value;
		}
	}

	/**
	 * Push an entry to the context
	 *
	 * Is used to build array instances inside the evaluator.
	 *
	 * @param mixed $value
	 * @param string $key
	 * @return \TYPO3\Eel\Context
	 * @throws EvaluationException
	 */
	public function push($value, $key = NULL) {
		if (!is_array($this->value)) {
			throw new EvaluationException('Array operation on non-array context', 1344418485);
		}
		if ($key === NULL) {
			$this->value[] = $value;
		} else {
			$this->value[$key] = $value;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		if (is_object($this->value) && !method_exists($this->value, '__toString')) {
			return '[object ' . get_class($this->value) . ']';
		}
		return (string)$this->value;
	}

}
namespace TYPO3\Eel;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A Eel evaluation context
 * 
 * It works as a variable container with wrapping of return values
 * for safe access without warnings (on missing properties).
 */
class Context extends Context_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 * @param mixed $value
	 */
	public function __construct() {
		$arguments = func_get_args();

		if (!array_key_exists(0, $arguments)) $arguments[0] = NULL;
		call_user_func_array('parent::__construct', $arguments);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {

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
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\Eel\Context');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Eel\Context', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Eel\Context', $propertyName, 'var');
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
}
#