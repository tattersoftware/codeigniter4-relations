<?php

use Tests\Support\DatabaseTestCase;
use Tests\Support\Entities\Factory;
use Tests\Support\Entities\Propertyless;
use Tests\Support\Models\ArrayModel;
use Tatter\Relations\Exceptions\RelationsException;

class ConstructTest extends DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->factories = new ArrayModel();
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
		$this->expectExceptionMessage('Class Tests\Support\Entities\Propertyless must have the table property to use relations');
		
		$factory = new Propertyless($row);
	}
}
