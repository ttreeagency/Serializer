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
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * JSON Serialize
 *
 * @Flow\Scope("singleton")
 * @api
 */
class Unserialize {

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
	 * Unserialize a value/array/object-graph from a JSON string representation.
	 *
	 * @param string $string JSON serialized value/array/object representation
	 * @return mixed The unserialized value, array or object-graph.
	 */
	public function unserialize($string) {
		$data = json_decode($string, true);
		return $this->unserializeValue($data);
	}

	/**
	 * Unserialize a value/array/object-graph from a JSON string representation.
	 *
	 * @param string $string JSON serialized value/array/object representation
	 * @return mixed The unserialized value, array or object-graph.
	 */
	function __invoke($string) {
		return $this->unserialize($string);
	}

	/**
	 * Unserialize an individual object/array/hash/value from a hash of properties.
	 *
	 * @param array $data hashed value representation
	 * @return mixed unserialized value
	 */
	protected function unserializeValue($data) {
		if (!is_array($data)) {
			if (is_string($data) && Functions::strlen($data) === 24) {
				$date = \DateTime::createFromFormat(\DateTime::ISO8601, $data);
				if ($date instanceof \DateTime) {
					return $date;
				}
			}
			return $data;
		}

		if (array_key_exists(JsonSerializer::CLASS_NAME, $data)) {
			if ($data[JsonSerializer::CLASS_NAME] === JsonSerializer::HASH) {
				unset($data[JsonSerializer::CLASS_NAME]);
				return $this->unserializeArray($data);
			}

			return $this->unserializeObject($data);
		}

		return $this->unserializeArray($data);
	}

	/**
	 * Unserialize a hash/array.
	 *
	 * @param array $data hash/array
	 * @return array unserialized hash/array
	 */
	protected function unserializeArray($data) {
		$array = array();

		foreach ($data as $key => $value) {
			$array[$key] = $this->unserializeValue($value);
		}

		return $array;
	}

	/**
	 * Unserialize an individual object from a hash of properties.
	 *
	 * @param array $data hash of object properties
	 * @return object unserialized object
	 */
	protected function unserializeObject($data) {
		$className = $data[JsonSerializer::CLASS_NAME];

		if ($className === JsonSerializer::STD_CLASS) {
			unset($data[JsonSerializer::CLASS_NAME]);
			return (object)$this->unserializeArray($data);
		}

		$object = $this->objectManager->get($className);

		foreach ($this->reflectionService->getClassPropertyNames($className) as $propertyName) {
			if (array_key_exists($propertyName, $data)) {
				$value = $this->unserializeValue($data[$propertyName]);
				ObjectAccess::setProperty($object, $propertyName, $value);
			}
		}

		return $object;
	}

}