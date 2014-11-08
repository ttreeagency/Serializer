<?php
namespace Ttree\Serializer\Tests\Unit\Json;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

use Ttree\Serializer\Json\Unserialize;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObject;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectTree;
use Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectWithFlowInternalProperty;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Testcase for the URI class
 *
 */
class UnserializeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function unserializeSupportScalarValue() {
		$unserializer = new Unserialize();
		$this->assertSame('Simple scalar value', $unserializer('"Simple scalar value"'));
	}

	/**
	 * @test
	 */
	public function unserializeSupportBooleanValue() {
		$unserializer = new Unserialize();
		$this->assertSame(TRUE, $unserializer('true'));
		$this->assertSame(FALSE, $unserializer('false'));
	}

	/**
	 * @test
	 */
	public function unserializeSupportNullValue() {
		$unserializer = new Unserialize();
		$this->assertSame(NULL, $unserializer('null'));
	}

	/**
	 * @test
	 */
	public function unserializeSupportIntegerValue() {
		$unserializer = new Unserialize();
		$this->assertSame(10, $unserializer(10));
	}

	/**
	 * @test
	 */
	public function unserializeSupportHashValue() {
		$unserializer = new Unserialize();
		$this->assertSame(array('Hello', 'World'), $unserializer('["Hello","World"]'));
	}

	/**
	 * @test
	 */
	public function unserializeSupportArrayValue() {
		$unserializer = new Unserialize();
		$this->assertSame(array('first' => 'Hello', 'last' => 'World'), $unserializer('{"first":"Hello","last":"World"}'));
	}

	/**
	 * @test
	 */
	public function unserializeSupportDateTimeValue() {
		$unserializer = new Unserialize();
		$date = $unserializer('"2014-11-06T14:15:51+0100"');
		$this->assertInstanceOf('\DateTime', $date);
		$this->assertSame('2014-11-06T14:15:51+0100', $date->format(\DateTime::ISO8601));
	}

	/**
	 * @test
	 */
	public function unserializeSupportStdClassValue() {
		$object = new \stdClass();
		$object->first = 'Hello';
		$object->last = 'World';

		$unserializer = new Unserialize();
		$newObject = $unserializer('{"first":"Hello","last":"World","#class":"stdClass"}');
		$this->assertInstanceOf('\stdClass', $newObject);
		$this->assertSame('Hello', $newObject->first);
		$this->assertSame('World', $newObject->last);
	}

	/**
	 * @test
	 */
	public function unserializeSupportSimpleObjectValue() {
		$objectManagerMock = $this->getMockBuilder('TYPO3\Flow\Object\ObjectManager')->disableOriginalConstructor()->getMock();
		$objectManagerMock->expects($this->any())->method('get')->willReturn(new SimpleObject());
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->any())->method('getClassPropertyNames')->willReturn(array('first', 'last'));
		$unserializer = new Unserialize();
		ObjectAccess::setProperty($unserializer, 'objectManager', $objectManagerMock, TRUE);
		ObjectAccess::setProperty($unserializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$newObject = $unserializer('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObject","first":"Bonjour","last":"Monde"}');
		$this->assertInstanceOf('Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObject', $newObject);
		$this->assertSame('Bonjour', $newObject->first);
		$this->assertSame('Monde', $newObject->last);
	}

	/**
	 * @test
	 */
	public function unserializeSupportSimpleObjectTreeValue() {
		$objectManagerMock = $this->getMockBuilder('TYPO3\Flow\Object\ObjectManager')->disableOriginalConstructor()->getMock();
		$objectManagerMock->expects($this->at(0))->method('get')->willReturn(new SimpleObjectTree());
		$objectManagerMock->expects($this->at(1))->method('get')->willReturn(new SimpleObject());
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->at(0))->method('getClassPropertyNames')->willReturn(array('first', 'last', 'object'));
		$reflectionServiceMock->expects($this->at(1))->method('getClassPropertyNames')->willReturn(array('first', 'last'));
		$unserialize = new Unserialize();
		ObjectAccess::setProperty($unserialize, 'objectManager', $objectManagerMock, TRUE);
		ObjectAccess::setProperty($unserialize, 'reflectionService', $reflectionServiceMock, TRUE);
		$newObject = $unserialize('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObjectTree","first":"Bonjour","last":"Monde","object":{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObject","first":"Hello World","last":"World"}}');
		$this->assertInstanceOf('Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectTree', $newObject);
		$this->assertSame('Bonjour', $newObject->first);
		$this->assertSame('Monde', $newObject->last);
		$this->assertInstanceOf('Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObject', $newObject->object);
		$this->assertSame('Hello World', $newObject->object->first);
		$this->assertSame('World', $newObject->object->last);
	}

	/**
	 * @test
	 */
	public function unserializeSkipFlowProxyInternalProperties() {
		$objectManagerMock = $this->getMockBuilder('TYPO3\Flow\Object\ObjectManager')->disableOriginalConstructor()->getMock();
		$objectManagerMock->expects($this->any())->method('get')->willReturn(new SimpleObjectWithFlowInternalProperty());
		$reflectionServiceMock = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$reflectionServiceMock->expects($this->any())->method('getClassPropertyNames')->willReturn(array('first', 'last', 'Flow_Aop_Proxy_invokeJoinpoint'));
		$unserializer = new Unserialize();
		ObjectAccess::setProperty($unserializer, 'objectManager', $objectManagerMock, TRUE);
		ObjectAccess::setProperty($unserializer, 'reflectionService', $reflectionServiceMock, TRUE);
		$newObject = $unserializer('{"#class":"Ttree\\\\Serializer\\\\Tests\\\\Unit\\\\Json\\\\Fixtures\\\\SimpleObjectWithFlowInternalProperty","first":"Bonjour","last":"Monde"}');
		$this->assertInstanceOf('Ttree\Serializer\Tests\Unit\Json\Fixtures\SimpleObjectWithFlowInternalProperty', $newObject);
		$this->assertSame('Bonjour', $newObject->first);
		$this->assertSame('Monde', $newObject->last);
	}

}
