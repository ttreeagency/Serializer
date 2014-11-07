<?php
namespace Ttree\Serializer\Json;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

use Ttree\Serializer\SerializerInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * JSON Serialize
 *
 * @Flow\Scope("singleton")
 * @api
 */
class JsonSerializer implements SerializerInterface {

	/**
	 * Hash token used to identify PHP classes
	 * @type string
	 */
	const CLASS_NAME = '#class';

	/**
	 * Standard class name
	 * @type string
	 */
	const STD_CLASS = 'stdClass';

	/**
	 * DateTime
	 * @type string
	 */
	const DATETIME = 'DateTime';

	/**
	 * Serialize a given PHP value/array/object-graph to a JSON representation.
	 *
	 * @param mixed $value The value, array, or object-graph to be serialized.
	 * @param bool $pretty true to enable "pretty" JSON formatting.
	 * @return string JSON serialized representation
	 */
	public function serialize($value, $pretty = TRUE) {
		$serializer = new Serialize($pretty);
		return $serializer($value);
	}

	/**
	 * Unserialize a value/array/object-graph from a JSON string representation.
	 *
	 * @param string $string JSON serialized value/array/object representation
	 * @return mixed The unserialized value, array or object-graph.
	 */
	public function unserialize($string) {
		$unserializer = new Unserialize();
		return $unserializer($string);
	}

}