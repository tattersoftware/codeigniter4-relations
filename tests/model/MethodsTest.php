<?php

namespace Tests\Support\Models;

use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class MethodsTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->factories = new FactoryModel();
        $this->lawyers   = new LawyerModel();
        $this->machines  = new MachineModel();
        $this->servicers = new ServicerModel();
        $this->workers   = new WorkerModel();
    }

    public function testReindex()
    {
        $factories = $this->factories->with(false)->findAll();

        $this->assertEquals([1, 3, 4], array_keys($factories));
    }

    public function testNotReindex()
    {
        $factories = $this->factories->with(false)->reindex(false)->findAll();

        $this->assertEquals([0, 1, 2], array_keys($factories));
    }

    public function testWithExplicit()
    {
        $worker  = $this->workers->with('factories')->find(1);
        $factory = $this->factories->with(false)->find(1);

        $this->assertEquals($factory, reset($worker->factories));
    }

    public function testWithImplicit()
    {
        $servicer = $this->servicers->find(1);
        $lawyer   = $this->lawyers->with(false)->find(1);

        $this->assertEquals($lawyer, reset($servicer->lawyers));
    }

    public function testBelongsToIsSingleton()
    {
        $machine = $this->machines->find(1);
        $factory = $this->factories->with(false)->find(1);

        $this->assertEquals($factory, $machine->factory);
    }

    public function testNestedRelations()
    {
        $servicer = $this->servicers->with('machines')->find(1);
        $factory  = $this->factories->with(false)->find(1);

        $this->assertEquals($factory, $servicer->machines[1]->factory);
    }

    public function testNestedRelationsNotTooDeep()
    {
        $factories = $this->factories->with('machines')->findAll();
        $factory   = $this->factories->with(false)->find(1);

        $this->assertEquals($factory, $factories[1]->machines[1]->factory);
        $this->assertTrue(isset($factories[1]->machines[1]->factory));
        // $this->assertObjectNotHasAttribute('machines', $factories[1]->machines[1]->factory);
    }

    public function testBelongsToNested()
    {
        $machines  = $this->machines->findAll();
        $factories = $this->factories->with(false)->findAll();

        $this->assertEquals($factories[1], $machines[1]->factory);

        $this->assertEquals($factories[1], $machines[3]->factory);

        $this->assertEquals($factories[3], $machines[6]->factory);
    }

    public function testWithDeletedRelations()
    {
        $worker = $this->workers->with('factories')->find(4);
        $this->assertCount(1, $worker->factories);

        $this->setPrivateProperty($this->workers, 'withDeletedRelations', ['factories']);
        $worker = $this->workers->with('factories')->find(4);
        $this->assertCount(2, $worker->factories);
    }
}
