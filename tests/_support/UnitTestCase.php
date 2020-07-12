<?php namespace Tests\Support;

use Config\Services;
use CodeIgniter\Test\CIUnitTestCase;

class UnitTestCase extends CIUnitTestCase
{
	/**
	 * Instance of the library.
	 */
	protected $schemas;

	public function setUp(): void
	{
		parent::setUp();
		
		// Configure and inject the Schemas service
		$config         = new \Tatter\Schemas\Config\Schemas();
		$config->silent = false;
		
		$schemas = new \Tatter\Schemas\Schemas($config);
        Services::injectMock('schemas', $schemas);
	}
}
