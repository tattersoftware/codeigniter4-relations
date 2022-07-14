<?php

use Tests\Support\DatabaseTestCase;
use Tests\Support\Models\FactoryModel;
use Tests\Support\Models\NormalModel;

/**
 * @internal
 */
final class PassThroughTest extends DatabaseTestCase
{
    private FactoryModel $extended;
    private NormalModel $normal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extended = new FactoryModel();
        $this->normal   = new NormalModel();
    }

    public function testFindId()
    {
        $result1 = $this->extended->find(1);
        $result2 = $this->normal->find(1);

        $this->assertEquals($result2, $result1);
    }

    public function testFindDeleted()
    {
        $result1 = $this->extended->find(2);
        $result2 = $this->normal->find(2);

        $this->assertEquals($result2, $result1);
    }

    public function testFirst()
    {
        $result1 = $this->extended->first();
        $result2 = $this->normal->first();

        $this->assertEquals($result2, $result1);
    }

    public function testFirstDeleted()
    {
        $result1 = $this->extended->onlyDeleted()->first();
        $result2 = $this->normal->onlyDeleted()->first();

        $this->assertEquals($result2, $result1);
    }

    public function testFindArray()
    {
        $result1 = $this->extended->find([1, 3]);
        $result2 = $this->normal->find([1, 3]);

        // Sort arrays to reindex and get in same order
        sort($result1);
        sort($result2);

        $this->assertEquals($result2, $result1);
    }

    public function testFindAll()
    {
        $result1 = $this->extended->findAll();
        $result2 = $this->normal->findAll();

        // Sort arrays to reindex and get in same order
        sort($result1);
        sort($result2);

        $this->assertEquals($result2, $result1);
    }

    public function testFindAllWithDeleted()
    {
        $result1 = $this->extended->withDeleted()->findAll();
        $result2 = $this->normal->withDeleted()->findAll();

        // Sort arrays to reindex and get in same order
        sort($result1);
        sort($result2);

        $this->assertEquals($result2, $result1);
    }

    public function testFindAllLimit()
    {
        $result1 = $this->extended->findAll(2, 1);
        $result2 = $this->normal->findAll(2, 1);

        // Sort arrays to reindex and get in same order
        sort($result1);
        sort($result2);

        $this->assertEquals($result2, $result1);
    }

    public function testFindSomeColumns()
    {
        $result1 = $this->extended->select('id, name')->find();
        $result2 = $this->normal->select('id, name')->find();

        // Sort arrays to reindex and get in same order
        sort($result1);
        sort($result2);

        $this->assertEquals($result2, $result1);
    }

    public function testFindNoPrimary()
    {
        $result1 = $this->extended->select('name')->find();
        $result2 = $this->normal->select('name')->find();

        $this->assertEquals($result2, $result1);
    }
}
