<?php

namespace Tests\Support\Models;

use Tatter\Relations\Exceptions\RelationsException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Entities\Factory;
use Tests\Support\Entities\Machine;
use Tests\Support\Entities\Servicer;

/**
 * @internal
 */
final class RelationsTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->row     = $this->db->table('factories')->where('id', 1)->get()->getRowArray();
        $this->factory = new Factory($this->row);
    }

    public function testUnknownTableFails()
    {
        $this->expectException(RelationsException::class);
        $this->expectExceptionMessage('Table not present in schema: racecars');

        $result = $this->factory->_getRelationship('racecars');

        $this->expectException(RelationsException::class);
        $this->expectExceptionMessage('Table not present in schema: starships');

        $result = $this->factory->relations('starships');
    }

    public function testGetRelationship()
    {
        $relation = $this->factory->_getRelationship('workers');
        $this->assertEquals('manyToMany', $relation->type);

        $relation = $this->factory->_getRelationship('machines');
        $this->assertEquals('hasMany', $relation->type);
    }

    public function testObjectsManyToMany()
    {
        $workers = $this->factory->relations('workers');

        $this->assertCount(4, $workers);
        $this->assertEquals('Delgado', $workers[3]->lastname);
    }

    public function testKeysManyToMany()
    {
        $workers = $this->factory->relations('workers', true);

        $this->assertEquals([1, 2, 3, 4], $workers);
    }

    public function testObjectsBelongsTo()
    {
        $machines = new MachineModel();
        $object   = $machines->with(false)->find(3);
        $machine  = new Machine((array) $object);

        $factory = $machine->relations('factories');

        $this->assertEquals($this->factory->uid, $factory->uid);
    }

    public function testKeysBelongsTo()
    {
        $machines = new MachineModel();
        $object   = $machines->with(false)->find(3);
        $machine  = new Machine((array) $object);

        $factoryId = $machine->relations('factories', true);

        $this->assertEquals($this->factory->id, $factoryId);
    }

    public function testArrayHasMany()
    {
        $servicers = new ServicerModel();
        $object    = $servicers->with(false)->find(2);
        $servicer  = new Servicer((array) $object);

        $lawyers = $servicer->relations('lawyers');

        $this->assertCount(3, $lawyers);
        $this->assertEquals('Slick Rick', $lawyers[1]['name']);
    }

    public function testRelationsStayLoaded()
    {
        $this->factory->relations('workers');

        $this->assertCount(4, $this->factory->workers);
        $this->assertEquals('Delgado', $this->factory->workers[3]->lastname);
    }

    public function testRelationsMatchModelWith()
    {
        $factories = new FactoryModel();
        $factory   = $factories->with('workers')->find(1);

        $workers = $this->factory->relations('workers');

        /** @var Factory $factory */
        $this->assertEquals($factory->workers, $workers);
    }

    public function testWithDeletedRelations()
    {
        $object   = (new MachineModel())->with(false)->find(4);
        $machine1 = new Machine((array) $object);
        $this->assertNull($machine1->factory);

        $machine2 = new class () extends Machine {
            protected array $withDeletedRelations = ['factories'];
        };
        $machine2->fill((array) $object);

        $result = $machine2->factory;

        $this->assertNotNull($result);
        $this->assertSame('widget', $result->uid);
    }
}
