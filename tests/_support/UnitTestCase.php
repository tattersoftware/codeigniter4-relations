<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tatter\Schemas\Config\Schemas;

/**
 * @internal
 */
abstract class UnitTestCase extends CIUnitTestCase
{
    /**
     * Instance of the library.
     */
    protected $schemas;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure and inject the Schemas service
        $config                    = new Schemas();
        $config->silent            = false;
        $config->ignoredNamespaces = [];

        $schemas = new \Tatter\Schemas\Schemas($config);
        Services::injectMock('schemas', $schemas);
    }
}
