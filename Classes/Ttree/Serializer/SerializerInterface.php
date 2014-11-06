<?php
namespace Ttree\Serializer;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

/**
 * Serializer Interface
 */
interface SerializerInterface {

	/**
	 * Serialize a given PHP value/array/object-graph
	 *
	 * @param mixed $value The value, array, or object-graph to be serialized.
	 * @return string serialized representation
	 */
	public function serialize($value);

	/**
	 * Unserialize a value/array/object-graph from a serialized representation.
	 *
	 * @param string $string serialized value/array/object representation
	 * @return mixed The unserialized value, array or object-graph.
	 */
	public function unserialize($string);

}