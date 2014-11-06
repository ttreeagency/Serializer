<?php
namespace Ttree\Serializer\Tests\Unit\Json;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

use Ttree\Serializer\Json\JsonSerializer;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Testcase for the URI class
 *
 */
class JsonSerializerTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function serializeReturnSerializedRepresentationOfTheGivenValue() {
		$jsonSerializer = new JsonSerializer();
		$this->assertSame('{"first":"Hello","last":"World"}', $jsonSerializer->serialize(array('first' => 'Hello', 'last' => 'World')));
	}

	/**
	 * @test
	 */
	public function unserializeReturnTheValueBasedOnSerializedRepresentation() {
		$jsonSerializer = new JsonSerializer();
		$this->assertSame(array('first' => 'Hello', 'last' => 'World'), $jsonSerializer->unserialize('{"first":"Hello","last":"World"}'));
	}

}
