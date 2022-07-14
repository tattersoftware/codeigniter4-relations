<?php

namespace Tests\Support;

use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
abstract class DatabaseTestCase extends UnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Should the database be refreshed before each test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * The name of a seed file used for all tests within this test case.
     *
     * @var string
     */
    protected $seed = 'Tests\Support\Database\Seeds\TestSeeder';

    /**
     * The path to where we can find the test Seeds directory.
     *
     * @var string
     */
    protected $basePath = SUPPORTPATH . 'Database/';

    /**
     * The namespace to help us find the migration classes.
     *
     * @var string
     */
    protected $namespace = 'Tests\Support';

    protected function setUp(): void
    {
        parent::setUp();

        cache()->clean();
    }
}
