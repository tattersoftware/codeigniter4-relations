<?php namespace CIModuleTests\Support;

use CodeIgniter\Config\Services;

class UnitTestCase extends \CodeIgniter\Test\CIUnitTestCase
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
