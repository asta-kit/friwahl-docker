<?php 
namespace TYPO3\Eel\Helper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Eel".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * Array helpers for Eel contexts
 */
class ArrayHelper_Original implements ProtectedContextAwareInterface {

	/**
	 * Concatenate arrays or values to a new array
	 *
	 * @param array|mixed $array1 First array or value
	 * @param array|mixed $array2 Second array or value
	 * @param array|mixed $array_ Optional variable list of additional arrays / values
	 * @return array The array with concatenated arrays or values
	 */
	public function concat($array1, $array2, $array_ = NULL) {
		$arguments = func_get_args();
		foreach ($arguments as &$argument) {
			if (!is_array($argument)) {
				$argument = array($argument);
			}
		}
		return call_user_func_array('array_merge', $arguments);
	}

	/**
	 * Join values of an array with a separator
	 *
	 * @param array $array Array with values to join
	 * @param string $separator A separator for the values
	 * @return string A string with the joined values separated by the separator
	 */
	public function join(array $array, $separator = ',') {
		return implode($separator, $array);
	}

	/**
	 * Extract a portion of an indexed array
	 *
	 * @param array $array The array (with numeric indices)
	 * @param string $begin
	 * @param string $end
	 * @return array
	 */
	public function slice(array $array, $begin, $end = NULL) {
		if ($end === NULL) {
			$end = count($array);
		} elseif ($end < 0) {
			$end = count($array) + $end;
		}
		$length = $end - $begin;
		return array_slice($array, $begin, $length);
	}

	/**
	 * Returns an array in reverse order
	 *
	 * @param array $array The array
	 * @return array
	 */
	public function reverse(array $array) {
		return array_reverse($array);
	}

	/**
	 * Get the array keys
	 *
	 * @param array $array The array
	 * @return array
	 */
	public function keys(array $array) {
		return array_keys($array);
	}

	/**
	 * Get the length of an array
	 *
	 * @param array $array The array
	 * @return integer
	 */
	public function length(array $array) {
		return count($array);
	}

	/**
	 * Check if an array is empty
	 *
	 * @param array $array The array
	 * @return boolean TRUE if the array is empty
	 */
	public function isEmpty(array $array) {
		return count($array) === 0;
	}

	/**
	 * Get the first element of an array
	 *
	 * @param array $array The array
	 * @return mixed
	 */
	public function first(array $array) {
		return reset($array);
	}

	/**
	 * Get the last element of an array
	 *
	 * @param array $array The array
	 * @return mixed
	 */
	public function last(array $array) {
		return end($array);
	}

	/**
	 * @param array $array
	 * @param mixed $searchElement
	 * @param integer $fromIndex
	 * @return mixed
	 */
	public function indexOf(array $array, $searchElement, $fromIndex = NULL) {
		if ($fromIndex !== NULL) {
			$array = array_slice($array, $fromIndex, NULL, TRUE);
		}
		$result = array_search($searchElement, $array, TRUE);
		if ($result === FALSE) {
			return -1;
		}
		return $result;
	}

	/**
	 * Picks a random element from the array
	 *
	 * @param array $array
	 * @return mixed A random entry or NULL if the array is empty
	 */
	public function random(array $array) {
		if ($array === array()) {
			return NULL;
		}
		$randomIndex = array_rand($array);
		return $array[$randomIndex];
	}

	/**
	 * Sorts an array
	 *
	 * The sorting is done first by numbers, then by characters.
	 *
	 * Internally natsort() is used as it most closely resembles javascript's sort().
	 * Because there are no real associative arrays in Javascript, keys of the array will be preserved.
	 *
	 * @param array $array
	 * @return array The sorted array
	 */
	public function sort(array $array) {
		if ($array === array()) {
			return $array;
		}
		natsort($array);
		$i = 0;
		$newArray = array();
		foreach ($array as $key => $value) {
			if (is_string($key)) {
				$newArray[$key] = $value;
			} else {
				$newArray[$i] = $value;
				$i++;
			}
		}
		return $newArray;
	}

	/**
	 * Shuffle an array
	 *
	 * Randomizes entries an array with the option to preserve the existing keys.
	 * When this option is set to FALSE, all keys will be replaced
	 *
	 * @param array $array
	 * @param boolean $preserveKeys Wether to preserve the keys when shuffling the array
	 * @return array The shuffled array
	 */
	public function shuffle(array $array, $preserveKeys = TRUE) {
		if ($array === array()) {
			return $array;
		}
		if ($preserveKeys) {
			$keys = array_keys($array);
			shuffle($keys);
			$shuffledArray = array();
			foreach ($keys as $key) {
				$shuffledArray[$key] = $array[$key];
			}
			$array = $shuffledArray;
		} else {
			shuffle($array);
		}
		return $array;
	}

	/**
	 * Removes the last element from an array
	 *
	 * Note: This differs from the JavaScript behavior of Array.pop which will return the popped element.
	 *
	 * An empty array will result in an empty array again.
	 *
	 * @param array $array
	 * @return array The array without the last element
	 */
	public function pop(array $array) {
		if ($array === array()) {
			return $array;
		}
		array_pop($array);
		return $array;
	}

	/**
	 * Insert one or more elements at the end of an array
	 *
	 * Allows to push multiple elements at once:
	 *
	 *     Array.push(array, e1, e2)
	 *
	 * @param array $array
	 * @param mixed $element
	 * @return array The array with the inserted elements
	 */
	public function push(array $array, $element) {
		$elements = func_get_args();
		array_shift($elements);
		foreach ($elements as $element) {
			array_push($array, $element);
		}
		return $array;
	}

	/**
	 * Remove the first element of an array
	 *
	 * Note: This differs from the JavaScript behavior of Array.shift which will return the shifted element.
	 *
	 * An empty array will result in an empty array again.
	 *
	 * @param array $array
	 * @return array The array without the first element
	 */
	public function shift(array $array) {
		array_shift($array);
		return $array;
	}

	/**
	 * Insert one or more elements at the beginning of an array
	 *
	 * Allows to insert multiple elements at once:
	 *
	 *     Array.unshift(array, e1, e2)
	 *
	 * @param array $array
	 * @param mixed $element
	 * @return array The array with the inserted elements
	 */
	public function unshift(array $array, $element) {
		// get all elements that are supposed to be added
		$elements = func_get_args();
		array_shift($elements);
		foreach($elements as $element) {
			array_unshift($array, $element);
		}
		return $array;
	}

	/**
	 * Replaces a range of an array by the given replacements
	 *
	 * Allows to give multiple replacements at once:
	 *
	 *     Array.splice(array, 3, 2, 'a', 'b')
	 *
	 * @param array $array
	 * @param integer $offset Index of the first element to remove
	 * @param integer $length Number of elements to remove
	 * @param mixed $replacements Elements to insert instead of the removed range
	 * @return array The array with removed and replaced elements
	 */
	public function splice(array $array, $offset, $length = 1, $replacements = NULL) {
		$arguments = func_get_args();
		$replacements = array_slice($arguments, 3);
		array_splice($array, $offset, $length, $replacements);
		return $array;
	}

	/**
	 * All methods are considered safe
	 *
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName) {
		return TRUE;
	}

}
namespace TYPO3\Eel\Helper;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Array helpers for Eel contexts
 */
class ArrayHelper extends ArrayHelper_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


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
	$reflectedClass = new \ReflectionClass('TYPO3\Eel\Helper\ArrayHelper');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Eel\Helper\ArrayHelper', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Eel\Helper\ArrayHelper', $propertyName, 'var');
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