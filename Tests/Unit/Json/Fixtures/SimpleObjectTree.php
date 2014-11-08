<?php
namespace Ttree\Serializer\Tests\Unit\Json\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Serializer".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        */

class SimpleObjectTree {
	public $first = 'Hello';
	public $last = 'World';
	public $object;

	public function __construct() {
		$this->object = new SimpleObject();
		$this->object->first = 'Hello World';
	}


}
