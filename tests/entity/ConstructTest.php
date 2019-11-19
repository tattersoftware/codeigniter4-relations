<?php

use CIModuleTests\Support\Entities\Factory;
use Tatter\Relations\Exceptions\RelationsException;

class ConstructTest extends CIModuleTests\Support\DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->factories = new \CIModuleTests\Support\Models\ArrayModel();
	}
	
	public function testConstructSuccess()
	{
		$row = $this->factories->find(1);

		$factory = new Factory($row);
		
        $this->assertEquals('test001', $factory->uid);
	}
	
	public function testConstructFailure()
	{
		$row = $this->factories->find(1);

		$this->expectException(RelationsException::class);
		$this->expectExceptionMessage('Class CIModuleTests\Support\Entities\Propertyless must have the table property to use relations');
		
		$factory = new \CIModuleTests\Support\Entities\Propertyless($row);
	}
}
