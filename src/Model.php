<?php namespace Tatter\Relations;

use CodeIgniter\Config\Services;
use Tatter\Relations\Exceptions\RelationsException;

class Model extends \CodeIgniter\Model
{
	// Static instance of the schema
	protected static $schema;
	
	// Static instance of Relations config
	protected static $config;
	
	// Array of related tables to fetch from when using finders
	protected $with = [];
	
	// Array of tables to block from loading relations
	protected $without = [];
	
	// Call CI model constructor then load the schema and register afterFind
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
        parent::__construct($db, $validation);
		
		// First model loads the schema & config
		if (is_null(self::$schema))
		{
			$schemas = Services::schemas();
			if (empty($schemas))
			{
				throw \RuntimeException(lang('Relations.noSchemas'));
			}
			
			// Check for a schema from cache
			$schema = $schemas->import('cache')->get();
			if (is_null($schema))
			{
				// Generate the schema and save it to the cache
				$schemas->import('database', 'model')->export('cache');
				$schema = $schemas->get();
			}
			
			self::$schema = $schema;
			self::$config = config('Relations');
		}
	}
	
	/**
	 * Adds related tables to load along with the next finder.
	 *
	 * @param mixed   $with       Table name, array of table names, or false (to disable)
	 * @param bool    $overwrite  Whether to merge with existing table 'with' list
	 *
	 * @return $this
	 */
	public function with($with, bool $overwrite = false)
	{
		// Check for a request to disable
		if ($with === false)
		{
			$this->tmpWith = [];
			return $this;
		}
		
		// Force a single table name into an array
		if (! is_array($with))
		{
			$with = [$with];
		}
		
		// Option to override this model's pre-seeded value
		if ($overwrite)
		{
			$this->tmpWith = $with;
		}
		else
		{
			$this->tmpWith = array_merge($this->with, $with);
		}
		
		return $this;
	}
	
	/**
	 * Blocks specified tables from being loaded as relations with the next finder.
	 * Used mostly to prevent nesting loops.
	 *
	 * @param mixed   $without    Table name or array of table names
	 *
	 * @return $this
	 */
	public function without($tables)
	{
		if (! is_string($tables) && ! is_array($tables))
		{
			throw \RuntimeException(lang('Relations.invalidWithout'));
		}
		
		if (is_string($tables))
		{
			$tables = [$tables];
		}
	
		$this->tmpWithout = array_merge($this->without, $tables);
		
		return $this;
	}

	//--------------------------------------------------------------------
	// FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param mixed|array|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id = null)
	{
		// Get data from the framework model as usual
		$data = parent::find($id);
		
		// For singletons, wrap them as a one-item array and then unwrap on return
		if (is_numeric($id) || is_string($id))
		{
			$data = $this->addRelated([$data]);
			return reset($data);
		}
		
		return $this->addRelated($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 *
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return array|null
	 */
	public function findAll(int $limit = 0, int $offset = 0)
	{
		$data = parent::findAll($limit, $offset);
		
		return $this->addRelated($data);		
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 *
	 * @return array|object|null
	 */
	public function first()
	{
		$data = parent::first();
		
		// For singletons, wrap them as a one-item array and then unwrap on return
		$data = $this->addRelated([$data]);
		return reset($data);
	}

	/**
	 * Intercepts data from a finder and injects related items
	 *
	 * @param array  $rows  Array of rows from the finder
	 *
	 * @return array
	 */
	protected function addRelated($rows): ?array
	{
		// If there were no matches then reset per-query data and quit
		if (empty($rows))
		{
			unset($this->tmpWith, $this->tmpWith);
			return $this->simpleReindex($rows);
		}
		
		// If no tmpWith was set then use this model's default
		if (! isset($this->tmpWith))
		{
			$this->tmpWith = $this->with;
		}
		// If no tmpWithout was set then use this model's default
		if (! isset($this->tmpWithout))
		{
			$this->tmpWithout = $this->without;
		}
		
		// Remove any blocked tables from the request
		$this->tmpWith = array_diff($this->tmpWith, $this->tmpWithout);
		
		// If tmpWith ends up empty then reset and quit
		if (empty($this->tmpWith))
		{
			unset($this->tmpWith, $this->tmpWith);
			return $this->simpleReindex($rows);
		}
		
		// Harvest the IDs that want relations
		$ids = array_column($rows, $this->primaryKey);
		
		// Find the relations for each table
		$relations = [];
		foreach ($this->tmpWith as $tableName)
		{
			$relations[$tableName] = $this->findRelated($tableName, $ids);
		}
		
		// Reindex $rows by this model's primary key and inject related items
		$return = [];
		foreach ($rows as $item)
		{
			// Handle array return types
			if (is_array($item))
			{
				$id = $item[$this->primaryKey];
				
				// Inject related items
				foreach ($relations as $tableName => $items)
				{
					$item[$tableName] = $items[$id] ?? [];
				}
			}
			// Handle object return types
			else
			{
				$id = $item->{$this->primaryKey};
				
				// Inject related items
				foreach ($relations as $tableName => $items)
				{
					$item->$tableName = $items[$id] ?? [];
				}
			}
			
			$return[$id] = $item;
		}
		
		// Clear old data and reset per-query properties
		unset($rows, $this->tmpWith, $this->tmpWith);
		
		return $return;
	}

	/**
	 * Uses the Schema to load related items
	 *
	 * @param string $tableName  Name of the table for related items
	 * @param array  $ids        Array of primary keys
	 *
	 * @return array
	 */
	public function findRelated($tableName, $ids): array
	{
		// Make sure the schema knows this table
		if (! isset(self::$schema->tables->{$tableName}))
		{			
			throw RelationsException::forUnknownTable($tableName);
		}
		// Fetch the related table for easy access
		$table = self::$schema->tables->{$tableName};

		// Make sure the model table is related to the requested table
		if (! isset(self::$schema->tables->{$this->table}->relations->{$tableName}))
		{
			throw RelationsException::forUnknownRelation($this->table, $tableName);
		}
		// Fetch the relation for easy access
		$relation = self::$schema->tables->{$this->table}->relations->{$tableName};
		
		// Verify pivots
		if (empty($relation->pivots))
		{
			throw SchemasException::forMissingPivots($this->table, $tableName);			
		}
		
		// Check for a known model for the target table
		if (isset($table->model))
		{
			// Grab an instance of the model to use as the builder
			$class      = $table->model;
			$builder    = new $class();
			$returnType = $builder->returnType;
			unset($class);
			
			// Check for another Relations model to prevent nesting loops
			if ($builder instanceof self)
			{
				// If nesting is allowed we need to disable this table
				// WIP: still won't prevent 3-level recursion loops
				if (self::$config->allowNesting)
				{
					$builder->without($this->table);
				}
				// Otherwise turn off relation loading on returned relations
				else
				{
					$builder->with(false);
				}
			}
		}
		// No model - use a generic builder
		else
		{
			$builder = $this->db->table($tableName);
			$returnType = self::$config->defaultReturnType;
		}
		
		// Define the returns
		$builder->select("{$tableName}.*");

		// We also need the model table's pivot ID in the return
		$pivot = reset($relation->pivots); // [table1, localKey, foreignKey]
		$builder->select("{$pivot[0]}.{$pivot[2]} AS originating_id");
		
		// Limit to the requested IDs
		$builder->whereIn("{$pivot[0]}.{$pivot[2]}", $ids);
		
		// Navigate the pivots to generate join statements
		$currentTable = $this->table;
		foreach ($relation->pivots as $pivot)
		{
			$builder->join($pivot[0], "{$currentTable}.{$pivot[1]} = {$pivot[0]}.{$pivot[2]}");
			$currentTable = $pivot[0];
		}
		
		// If the model is available use it to get the result
		if (isset($table->model))
		{
			$results = $builder->find();
		}
		// No model - use a generic getResult
		else
		{
			$results = $builder->get()->getResult();
		}
		
		// Clean up
		unset($table, $relation, $builder);
		
		// Reindex the results by this model's primary key
		$return = [];
		if ($returnType == 'array')
		{
			foreach ($results as $row)
			{
				$originatingId = $row['originating_id'];
				unset($row['originating_id']);
				$return[$originatingId][] = $row;
			}
		}
		else
		{
			foreach ($results as $row)
			{
				$originatingId = $row->originating_id;
				unset($row->originating_id);
				$return[$originatingId][] = $row;
			}
		}
		unset($results);

		return $return;
	}
	
	/**
	 * Reindexes $rows from a finder by their primary KEY_AS_FILENAME
	 * Mostly used for consistent return format when no relations are requested
	 *
	 * @param array  $rows  Array of rows from the finder
	 *
	 * @return array
	 */
	protected function simpleReindex($rows): array
	{
		// Reindex $rows by this model's primary key and inject related items
		$return = [];
		foreach ($rows as $item)
		{
			// Handle array return types
			if (is_array($item))
			{
				$id = $item[$this->primaryKey];
			}
			// Handle object return types
			else
			{
				$id = $item->{$this->primaryKey};
			}
			
			$return[$id] = $item;
		}
		
		return $return;
	}
}
