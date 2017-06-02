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

use TYPO3\Eel\EvaluationException;
use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Unicode\Functions as UnicodeFunctions;
use TYPO3\Flow\Utility\Unicode\TextIterator;

/**
 * String helpers for Eel contexts
 */
class StringHelper_Original implements ProtectedContextAwareInterface {

	/**
	 * Return the characters in a string from start up to the given length
	 *
	 * This implementation follows the JavaScript specification for "substr".
	 *
	 * Examples:
	 *
	 *   String.substr('Hello, World!', 7, 5) === 'World'
	 *
	 *   String.substr('Hello, World!', 7) === 'World!'
	 *
	 *   String.substr('Hello, World!', -6) === 'World!'
	 *
	 * @param string $string A string
	 * @param integer $start Start offset
	 * @param integer $length Maximum length of the substring that is returned
	 * @return string The substring
	 */
	public function substr($string, $start, $length = NULL) {
		if ($length === NULL) {
			$length = mb_strlen($string, 'UTF-8');
		}
		$length = max(0, $length);
		return mb_substr($string, $start, $length, 'UTF-8');
	}

	/**
	 * Return the characters in a string from a start index to an end index
	 *
	 * This implementation follows the JavaScript specification for "substring".
	 *
	 * Examples:
	 *
	 *   String.substring('Hello, World!', 7, 12) === 'World'
	 *
	 *   String.substring('Hello, World!', 7) === 'World!'
	 *
	 * @param string $string
	 * @param integer $start Start index
	 * @param integer $end End index
	 * @return string The substring
	 */
	public function substring($string, $start, $end = NULL) {
		if ($end === NULL) {
			$end = mb_strlen($string, 'UTF-8');
		}
		$start = max(0, $start);
		$end = max(0, $end);
		if ($start > $end) {
			$temp = $start;
			$start = $end;
			$end = $temp;
		}
		$length = $end - $start;
		return mb_substr($string, $start, $length, 'UTF-8');
	}

	/**
	 * @param string $string
	 * @param integer $index
	 * @return string The character at the given index
	 */
	public function charAt($string, $index) {
		if ($index < 0) {
			return '';
		}
		return mb_substr($string, $index, 1, 'UTF-8');
	}

	/**
	 * Test if a string ends with the given search string
	 *
	 * Examples:
	 *
	 *   String.endsWith('Hello, World!', 'World!') === true
	 *
	 * @param string $string The string
	 * @param string $search A string to search
	 * @param integer $position Optional position for limiting the string
	 * @return boolean TRUE if the string ends with the given search
	 */
	public function endsWith($string, $search, $position = NULL) {
		$position = $position !== NULL ? $position : mb_strlen($string, 'UTF-8');
		$position = $position - mb_strlen($search, 'UTF-8');
		return mb_strrpos($string, $search, NULL, 'UTF-8') === $position;
	}

	/**
	 * @param string $string
	 * @param string $search
	 * @param integer $fromIndex
	 * @return integer
	 */
	public function indexOf($string, $search, $fromIndex = NULL) {
		$fromIndex = max(0, $fromIndex);
		if ($search === '') {
			return min(mb_strlen($string, 'UTF-8'), $fromIndex);
		}
		$index = mb_strpos($string, $search, $fromIndex, 'UTF-8');
		if ($index === FALSE) {
			return -1;
		}
		return (integer)$index;
	}

	/**
	 * @param string $string
	 * @param string $search
	 * @param integer $toIndex
	 * @return integer
	 */
	public function lastIndexOf($string, $search, $toIndex = NULL) {
		$length = mb_strlen($string, 'UTF-8');
		if ($toIndex === NULL) {
			$toIndex = $length;
		}
		$toIndex = max(0, $toIndex);
		if ($search === '') {
			return min($length, $toIndex);
		}
		$string = mb_substr($string, 0, $toIndex, 'UTF-8');
		$index = mb_strrpos($string, $search, 0, 'UTF-8');
		if ($index === FALSE) {
			return -1;
		}
		return (integer)$index;
	}

	/**
	 * This method is deprecated. @see pregMatch()
	 *
	 * @param string $string
	 * @param string $pattern
	 * @return array The matches as array or NULL if not matched
	 * @deprecated Use pregMatch() instead
	 */
	public function match($string, $pattern) {
		return $this->pregMatch($string, $pattern);
	}

	/**
	 * Match a string with a regular expression (PREG style)
	 *
	 * @param string $string
	 * @param string $pattern
	 * @return array The matches as array or NULL if not matched
	 * @throws EvaluationException
	 */
	public function pregMatch($string, $pattern) {
		$number = preg_match($pattern, $string, $matches);
		if ($number === FALSE) {
			throw new EvaluationException('Error evaluating regular expression ' . $pattern . ': ' . preg_last_error(), 1372793595);
		}
		if ($number === 0) {
			return NULL;
		}
		return $matches;
	}

	/**
	 * Replace occurrences of a search string inside the string using regular expression matching (PREG style)
	 *
	 * @param string $string
	 * @param string $pattern
	 * @param string $replace
	 * @return string The string with all occurrences replaced
	 */
	public function pregReplace($string, $pattern, $replace) {
		return preg_replace($pattern, $replace, $string);
	}

	/**
	 * Replace occurrences of a search string inside the string
	 *
	 * Note: this method does not perform regular expression matching, @see pregReplace().
	 *
	 * @param string $string
	 * @param string $search
	 * @param string $replace
	 * @return string The string with all occurrences replaced
	 */
	public function replace($string, $search, $replace) {
		return str_replace($search, $replace, $string);
	}

	/**
	 * Split a string by a separator
	 *
	 * Node: This implementation follows JavaScript semantics without support of regular expressions.
	 *
	 * @param string $string
	 * @param string $separator
	 * @param integer $limit
	 * @return array
	 */
	public function split($string, $separator = NULL, $limit = NULL) {
		if ($separator === NULL) {
			return array($string);
		}
		if ($separator === '') {
			$result = str_split($string);
			if ($limit !== NULL) {
				$result = array_slice($result, 0, $limit);
			}
			return $result;
		}
		if ($limit === NULL) {
			$result = explode($separator, $string);
		} else {
			$result = explode($separator, $string, $limit);
		}
		return $result;
	}

	/**
	 * Test if a string starts with the given search string
	 *
	 * @param string $string
	 * @param string $search
	 * @param integer $position
	 * @return boolean
	 */
	public function startsWith($string, $search, $position = NULL) {
		$position = $position !== NULL ? $position : 0;
		return mb_strrpos($string, $search, NULL, 'UTF-8') === $position;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function toLowerCase($string) {
		return mb_strtolower($string, 'UTF-8');
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function toUpperCase($string) {
		return mb_strtoupper($string, 'UTF-8');
	}

	/**
	 * Strip all tags from the given string
	 *
	 * This is a wrapper for the strip_tags() PHP function.
	 *
	 * @param string $string
	 * @return string
	 */
	public function stripTags($string) {
		return strip_tags($string);
	}

	/**
	 * Test if the given string is blank (empty or consists of whitespace only)
	 *
	 * @param string $string
	 * @return boolean TRUE if the given string is blank
	 */
	public function isBlank($string) {
		return trim((string)$string) === '';
	}

	/**
	 * Trim whitespace at the beginning and end of a string
	 *
	 *
	 *
	 * @param string $string
	 * @param string $charlist Optional list of characters that should be trimmed, defaults to whitespace
	 * @return string
	 */
	public function trim($string, $charlist = NULL) {
		if ($charlist === NULL) {
			return trim($string);
		} else {
			return trim($string, $charlist);
		}
	}

	/**
	 * Convert the given value to a string
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function toString($value) {
		return (string)$value;
	}

	/**
	 * Convert a string to integer
	 *
	 * @param string $string
	 * @return string
	 */
	public function toInteger($string) {
		return (integer)$string;
	}

	/**
	 * Convert a string to float
	 *
	 * @param string $string
	 * @return string
	 */
	public function toFloat($string) {
		return (float)$string;
	}

	/**
	 * Convert a string to boolean
	 *
	 * A value is TRUE, if it is either the string "TRUE" or "true" or the number "1".
	 *
	 * @param string $string
	 * @return string
	 */
	public function toBoolean($string) {
		return strtolower($string) === 'true' || (integer)$string === 1;
	}

	/**
	 * Encode the string for URLs according to RFC 3986
	 *
	 * @param string $string
	 * @return string
	 */
	public function rawUrlEncode($string) {
		return rawurlencode($string);
	}

	/**
	 * Decode the string from URLs according to RFC 3986
	 *
	 * @param string $string
	 * @return string
	 */
	public function rawUrlDecode($string) {
		return rawurldecode($string);
	}

	/**
	 * @param string $string
	 * @param boolean $preserveEntities TRUE if entities should not be double encoded
	 * @return string
	 */
	public function htmlSpecialChars($string, $preserveEntities = FALSE) {
		return htmlspecialchars($string, NULL, NULL, !$preserveEntities);
	}

	/**
	 * Crop a string to $maximumCharacters length, optionally appending $suffix if cropping was necessary.
	 *
	 * @param string $string the input string
	 * @param integer $maximumCharacters number of characters where cropping should happen
	 * @param string $suffix optional suffix to be appended if cropping was necessary
	 * @return string the cropped string
	 */
	public function crop($string, $maximumCharacters, $suffix = '') {
		if (UnicodeFunctions::strlen($string) > $maximumCharacters) {
			$string = UnicodeFunctions::substr($string, 0, $maximumCharacters);
			$string .= $suffix;
		}

		return $string;
	}

	/**
	 * Crop a string to $maximumCharacters length, taking words into account,
	 * optionally appending $suffix if cropping was necessary.
	 *
	 * @param string $string the input string
	 * @param integer $maximumCharacters number of characters where cropping should happen
	 * @param string $suffix optional suffix to be appended if cropping was necessary
	 * @return string the cropped string
	 */
	public function cropAtWord($string, $maximumCharacters, $suffix = '') {
		if (UnicodeFunctions::strlen($string) > $maximumCharacters) {
			$iterator = new TextIterator($string, TextIterator::WORD);
			$string = UnicodeFunctions::substr($string, 0, $iterator->preceding($maximumCharacters));
			$string .= $suffix;
		}

		return $string;
	}

	/**
	 * Crop a string to $maximumCharacters length, taking sentences into account,
	 * optionally appending $suffix if cropping was necessary.
	 *
	 * @param string $string the input string
	 * @param integer $maximumCharacters number of characters where cropping should happen
	 * @param string $suffix optional suffix to be appended if cropping was necessary
	 * @return string the cropped string
	 */
	public function cropAtSentence($string, $maximumCharacters, $suffix = '') {
		if (UnicodeFunctions::strlen($string) > $maximumCharacters) {
			$iterator = new TextIterator($string, TextIterator::SENTENCE);
			$string = UnicodeFunctions::substr($string, 0, $iterator->preceding($maximumCharacters));
			$string .= $suffix;
		}

		return $string;
	}

	/**
	 * Calculates the MD5 checksum of the given string
	 *
	 * @param string $string
	 * @return string
	 */
	public function md5($string) {
		return md5($string);
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
 * String helpers for Eel contexts
 */
class StringHelper extends StringHelper_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


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
	$reflectedClass = new \ReflectionClass('TYPO3\Eel\Helper\StringHelper');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Eel\Helper\StringHelper', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Eel\Helper\StringHelper', $propertyName, 'var');
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