<?php namespace CIModuleTests\Support;

use CodeIgniter\Config\Services;
use Tatter\Schemas\Handlers\DatabaseHandler;

class DatabaseTestCase extends \CodeIgniter\Test\CIDatabaseTestCase
{
	/**
	 * Should the database be refreshed before each test?
	 *
	 * @var boolean
	 */
	protected $refresh = true;

	/**
	 * The name of a seed file used for all tests within this test case.
	 *
	 * @var string
	 */
	protected $seed = 'CIModuleTests\Support\Database\Seeds\TestSeeder';

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
	protected $namespace = 'CIModuleTests\Support';

	/**
	 * Preconfigured config instance.
	 */
	protected $config;

	/**
	 * Instance of the library.
	 */
	protected $schemas;

	/**
	 * SchemaDatabaseHandler preloaded for 'tests' DBGroup.
	 */
	protected $handler;

	/**
	 * An initial schema generated from the 'tests' database.
	 */
	protected $schema;

	public function setUp(): void
	{
		parent::setUp();
	}
}
