<?php namespace Tests\Support;

use Tatter\Schemas\Config\Schemas;
use Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

class UnitTestCase extends CIUnitTestCase
{
	/**
	 * Instance of the library.
	 */
	protected $schemas;

	protected function setUp(): void
	{
		parent::setUp();
		
		// Configure and inject the Schemas service
		$config         = new Schemas();
		$config->silent = false;
		$config->ignoredNamespaces = [];
		
		$schemas = new \Tatter\Schemas\Schemas($config);
        Services::injectMock('schemas', $schemas);
	}
}
