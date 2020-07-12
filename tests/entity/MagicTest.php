<?php namespace Tests\Support\Models;

use Tests\Support\DatabaseTestCase;
use Tests\Support\Entities\Factory;
use Tests\Support\Entities\Machine;
use Tests\Support\Entities\Propertyless;
use Tatter\Relations\Exceptions\RelationsException;

class MagicTest extends DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->factories = new ArrayModel();
		$this->factory   = new Factory($this->factories->find(1));
		
		$this->machines = new MachineModel();
		$this->machine  = new Machine((array) $this->machines->with(false)->find(7));
	}

	public function testGetIgnoresUnmatched()
	{
        $this->assertNull($this->factory->racecars);
	}
	
	public function testRequiresProperties()
	{
		$row = $this->factories->find(1);

		$this->expectException(RelationsException::class);
		$this->expectExceptionMessage('Class Tests\Support\Entities\Propertyless must have the table property to use relations');
		
		$factory = (new Propertyless($row))->_getRelationship('foobar');
	}

	public function testGetSuccess()
	{
		$workers = $this->factory->workers;

		$this->assertCount(4, $workers);
		$this->assertEquals('Delgado', $workers[3]->lastname);
	}

	public function testGetSingleton()
	{
		$factory = $this->machine->factory;

		$this->assertEquals('evil-maker', $factory->uid);
	}

	public function testCallUnmatchedFails()
	{
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Method Tests\Support\Entities\Factory::doesJingle does not exist.');
		
		$this->factory->doesJingle();
	}

	public function testCallCaseFails()
	{
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage('Method Tests\Support\Entities\Factory::hasworkers does not exist.');
		
		$this->factory->hasworkers();
	}

	public function testCallTooManyArgsFails()
	{
		$this->expectException(\ArgumentCountError::class);
		$this->expectExceptionMessage('Too many arguments to function Tests\Support\Entities\Factory::hasWorkers, 4 passed and at most 1 expected.');
		
		$this->factory->hasWorkers([1], 2, 3, 4);
	}

	public function testCallHasSuccess()
	{
		$this->assertTrue($this->factory->hasWorkers([2, 4]));
	}

	public function testCallSingleton()
	{
		$this->assertTrue($this->factory->hasWorkers(1));
	}

	public function testCallSingular()
	{
		$this->assertTrue($this->factory->hasWorker(1));
	}

	public function testCallIgnoresDupes()
	{
		$this->assertTrue($this->factory->hasWorkers([2, 2, 2, 2]));		
	}

	public function testAddSuccess()
	{
		$result = $this->factory->addWorker(9);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(5, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testRemove()
	{
		$result = $this->factory->removeWorker([2, 3]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(2, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testSet()
	{
		$result = $this->factory->setWorkers(1);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(1, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testSetEmpty()
	{
		$result = $this->factory->setWorkers([]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(0, $builder->where('factory_id', 1)->countAllResults());
	}
}
