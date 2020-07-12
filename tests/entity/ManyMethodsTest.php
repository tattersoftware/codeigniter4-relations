<?php namespace Tests\Support\Models;

use Tests\Support\DatabaseTestCase;
use Tests\Support\Entities\Factory;
use Tatter\Relations\Exceptions\RelationsException;

class ManyMethodsTest extends DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->factories = new ArrayModel();
		$this->factory   = new Factory($this->factories->find(1));
	}

	public function testHasAnySuccess()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_has');
		
		$this->assertTrue($method('workers'));
	}

	public function testHasAnyFail()
	{
		$factory = new Factory($this->factories->find(4));
		$method = $this->getPrivateMethodInvoker($factory, '_has');
		
		$this->assertFalse($method('workers'));
	}

	public function testHasEverySuccess()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_has');
		
		$this->assertTrue($method('workers', [2, 4]));
	}

	public function testHasEveryFail()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_has');
		
		$this->assertFalse($method('workers', [4, 5]));
	}

	public function testAdd()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_add');
		$result = $method('workers', [9]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(5, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testAddMany()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_add');
		$result = $method('workers', [5, 6, 7, 8, 9]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(9, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testAddEmptyFails()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_add');
		$result = $method('workers', []);
		
		$this->assertFalse($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(4, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testRemove()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_remove');
		$result = $method('workers', [2, 3]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(2, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testRemoveEmptyFails()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_remove');
		$result = $method('workers', [ ]);
		
		$this->assertFalse($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(4, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testSet()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_set');
		$result = $method('workers', [9]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(1, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testSetMany()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_set');
		$result = $method('workers', [1, 3, 5, 7, 9]);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(5, $builder->where('factory_id', 1)->countAllResults());
	}

	public function testSetEmpty()
	{
		$method = $this->getPrivateMethodInvoker($this->factory, '_set');
		$result = $method('workers', []);
		
		$this->assertTrue($result);
		
		$builder = $this->db->table('factories_workers');

		$this->assertEquals(0, $builder->where('factory_id', 1)->countAllResults());
	}
}
