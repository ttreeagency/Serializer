<?php
namespace Ttree\Serializer\Tests\Unit\Json;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

use Ttree\Serializer\Json\Serialize;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObject;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectTree;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectWithFlowInternalProperty;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Testcase for the URI class
 *
 */
class SerializeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function serializeSupportScalarValue() {
		$serializer = new Serialize();
		$this->assertSame('"Simple scalar value"', $serializer('Simple scalar value'));
	}

	/**
	 * @test
	 */
	public function serializeSupportBooleanValue() {
		$serializer = new Serialize();
		$this->assertSame('true', $serializer(TRUE));
		$this->assertSame('false', $serializer(FALSE));
	}

	/**
	 * @test
	 */
	public function serializeSupportNullValue() {
		$serializer = new Serialize();
		$this->assertSame('null', $serializer(NULL));
	}

	/**
	 * @test
	 */
	public function serializeSupportIntegerValue() {
		$serializer = new Serialize();
		$this->assertSame(10, $serializer(10));
	}

	/**
	 * @test
	 */
	public function serializeSupportHashValue() {
		$serializer = new Serialize();
		$this->assertSame('["Hello","World"]', $serializer(array('Hello', 'World')));
	}

	/**
	 * @test
	 */
	public function serializeSupportArrayValue() {
		$serializer = new Serialize();
		$this->assertSame('{"first":"Hello","last":"World"}', $serializer(array('first' => 'Hello', 'last' => 'World')));
	}

	/**
	 * @test
	 */
	public function serializeSupportDateTimeValue() {
		$date = \DateTime::createFromFormat(\DateTime::ISO8601, '2014-11-06T14:15:51+0100');
		$serializer = new Serialize();
		$this->assertSame('"2014-11-06T14:15:51+0100"', $serializer($date));
	}

	/**
	 * @test
	 */
	public function serializeSupportStdClassValue() {
		$object = new \stdClass();
		$object->first = 'Hello';
		$object->last = 'World';

		$serializer = new Serialize();
		$this->assertSame('{"first":"Hello","last":"World","#class":"stdClass"}', $serializer($object));
		$this->assertSame('{
  "first": "Hello",
  "last": "World",
  "#class": "stdClass"
}', $serializer($object, TRUE));
	}

	/**
	 * @test
	 */
	public function serializeSupportSimpleObjectValue() {
		$object = new SimpleObject();
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->once())->method('getClassPropertyNames')->willReturn(array('first', 'last'));
		$reflectionServiceMock->expects($this->any())->method('getPropertyNamesByAnnotation')->willReturn(array());
		$serializer = new Serialize();
		ObjectAccess::setProperty($serializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$this->assertSame('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObject","first":"Hello","last":"World"}', $serializer($object));
	}

	/**
	 * @test
	 */
	public function serializeSupportSkippedPropertyWithSimpleObjectValue() {
		$object = new SimpleObject();
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->once())->method('getClassPropertyNames')->willReturn(array('first', 'last'));
		$reflectionServiceMock->expects($this->any())->method('getPropertyNamesByAnnotation')->willReturn(array('first' => TRUE));
		$serializer = new Serialize();
		ObjectAccess::setProperty($serializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$this->assertSame('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObject","last":"World"}', $serializer($object));
	}

	/**
	 * @test
	 */
	public function serializeSupportSimpleObjectTreeValue() {
		$object = new SimpleObjectTree();
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->any())->method('getClassPropertyNames')->willReturn(array('first', 'last', 'object'));
		$reflectionServiceMock->expects($this->any())->method('getPropertyNamesByAnnotation')->willReturn(array());
		$serializer = new Serialize();
		ObjectAccess::setProperty($serializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$this->assertSame('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObjectTree","first":"Hello","last":"World","object":{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObject","first":"Hello World","last":"World"}}', $serializer($object));
	}

	/**
	 * @test
	 */
	public function serializeSkipFlowProxyInternalProperties() {
		$object = new SimpleObjectWithFlowInternalProperty();
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->any())->method('getClassPropertyNames')->willReturn(array('first', 'last', 'Flow_Aop_Proxy_invokeJoinpoint'));
		$reflectionServiceMock->expects($this->any())->method('getPropertyNamesByAnnotation')->willReturn(array());
		$serializer = new Serialize();
		ObjectAccess::setProperty($serializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$this->assertSame('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObjectWithFlowInternalProperty","first":"Hello","last":"World"}', $serializer($object));
	}

}
