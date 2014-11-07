<?php
namespace Ttree\Serializer\Json;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use stdClass;
use DateTime;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Reflection\ReflectionService;
use TYPO3\Flow\Utility\Unicode\Functions;

/**
 * JSON Serialize
 *
 * @api
 */
class Serialize {

	/**
	 * One level of indentation
	 * @var string
	 */
	protected $indentation = '';

	/**
	 * Newline character(s)
	 * @var string
	 */
	protected $newline = "";

	/**
	 * Padding character(s) after ":" in JSON objects
	 * @var string
	 */
	protected $padding = '';

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * Serialize a given PHP value/array/object-graph to a JSON representation.
	 *
	 * @param mixed $value The value, array, or object-graph to be serialized.
	 * @param boolean $pretty true to enable "pretty" JSON formatting.
	 * @return string JSON serialized representation
	 */
	public function serialize($value, $pretty = FALSE) {
		if ($pretty) {
			$this->indentation = '  ';
			$this->newline = "\n";
			$this->padding = ' ';
		}
		return $this->serializeValue($value, 0);
	}

	/**
	 * Serialize a given PHP value/array/object-graph to a JSON representation.
	 *
	 * @param mixed $value The value, array, or object-graph to be serialized.
	 * @param boolean $pretty true to enable "pretty" JSON formatting.
	 * @return string JSON serialized representation
	 */
	function __invoke($value, $pretty = FALSE) {
		return $this->serialize($value, $pretty);
	}

	/**
	 * Serializes an individual object/array/hash/value, returning a JSON string representation
	 *
	 * @param mixed $value the value to serialize
	 * @param int $indent indentation level
	 *
	 * @return string JSON serialized value
	 */
	protected function serializeValue($value, $indent = 0) {
		$serializedValue =  'null';
		if (is_object($value)) {
			switch (get_class($value)) {
				case JsonSerializer::STD_CLASS:
					$serializedValue = $this->serializeStdClass($value, $indent);
					break;
				case JsonSerializer::DATETIME:
					$serializedValue = $this->serializeDateTime($value, $indent);
					break;
				default:
					$serializedValue = $this->serializeObject($value, $indent);
			}
		} else {
			if (is_array($value)) {
				if (array_keys($value) === array_keys(array_values($value))) {
					$serializedValue =  $this->serializeArray($value, $indent);
				} else {
					$serializedValue =  $this->serializeHash($value, $indent);
				}
			} else {
				if (is_numeric($value)) {
					$serializedValue = $value;
				} elseif (is_scalar($value)) {
					$serializedValue =  json_encode($value);
				}
			}
		}

		return $serializedValue;
	}

	/**
	 * Serializes a stdClass object returning a JSON string representation.
	 *
	 * @param stdClass $value stdClass object
	 * @param int $indent indentation level
	 *
	 * @return string JSON object representation
	 */
	protected function serializeStdClass($value, $indent) {
		$array = (array)$value;

		$array[JsonSerializer::CLASS_NAME] = JsonSerializer::STD_CLASS;

		return $this->serializeHash($array, $indent);
	}

	/**
	 * Serializes a DateTime object returning a ISO8601 representation.
	 *
	 * @param DateTime $value DateTime object
	 * @param int $indent indentation level
	 *
	 * @return string JSON object representation
	 */
	protected function serializeDateTime(DateTime $value, $indent) {
		return json_encode($value->format(DateTime::ISO8601));
	}

	/**
	 * Serializes a "wild" array (e.g. a "hash" array with mixed keys) returning a JSON string representation.
	 *
	 * @param array $hash hash array
	 * @param int $indent indentation level
	 *
	 * @return string JSON hash representation
	 */
	protected function serializeHash($hash, $indent) {
		$whitespace = $this->newline . str_repeat($this->indentation, $indent + 1);

		$string = '{';

		$comma = '';

		foreach ($hash as $key => $item) {
			$string .= $comma
				. $whitespace
				. json_encode($key)
				. ':'
				. $this->padding
				. $this->serializeValue($item, $indent + 1);

			$comma = ',';
		}

		$string .= $this->newline . str_repeat($this->indentation, $indent) . '}';

		return $string;
	}

	/**
	 * Serializes a complete object with aggregates, returning a JSON string representation.
	 *
	 * @param object $object object
	 * @param int $indent indentation level
	 *
	 * @return string JSON object representation
	 */
	protected function serializeObject($object, $indent) {
		$className = get_class($object);

		$whitespace = $this->newline . str_repeat($this->indentation, $indent + 1);

		$string = '{' . $whitespace . '"' . JsonSerializer::CLASS_NAME . '":' . $this->padding . json_encode($className);
		
		foreach ($this->reflectionService->getClassPropertyNames($className) as $propertyName) {
			if ($this->skipProperty($object, $className, $propertyName)) {
				continue;
			}
			$string .= ','
				. $whitespace
				. json_encode($propertyName)
				. ':'
				. $this->padding
				. $this->serializeValue(ObjectAccess::getProperty($object, $propertyName), $indent + 1);
		}

		$string .= $this->newline . str_repeat($this->indentation, $indent) . '}';

		return $string;
	}

	/**
	 * Check if a property must be skipped
	 *
	 * @param object $object
	 * @param string $className
	 * @param string $propertyName
	 * @return boolean
	 */
	protected function skipProperty($object, $className, $propertyName) {
		if (Functions::substr($propertyName, 0, 5) === 'Flow_') {
			return TRUE;
		}
		if (!ObjectAccess::isPropertyGettable($object, $propertyName)) {
			return TRUE;
		}
		$propertiesToSkip = $this->reflectionService->getPropertyNamesByAnnotation($className, 'Ttree\Serializer\Annotations\Skip');
		$transientProperties = $this->reflectionService->getPropertyNamesByAnnotation($className, 'TYPO3\Flow\Annotations\Transient');
		if (array_key_exists($propertyName, $propertiesToSkip) || array_key_exists($propertyName, $transientProperties)) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Serializes a "strict" array (base-0 integer keys) returning a JSON string representation.
	 *
	 * @param array $array array
	 * @param int $indent indentation level
	 *
	 * @return string JSON array representation
	 */
	protected function serializeArray($array, $indent) {
		$string = '[';

		$last_key = count($array) - 1;

		foreach ($array as $key => $item) {
			$string .= $this->serializeValue($item, $indent) . ($key === $last_key ? '' : ',');
		}

		$string .= ']';

		return $string;
	}

}