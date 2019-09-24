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
	
	// Call the CI model constructor then check for and load the schema
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
        parent::__construct($db, $validation);

		// First instantiation loads the config & inflection helper
		if (is_null(self::$config))
		{
			self::$config = config('Relations');
			helper('inflector');
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
			throw new \RuntimeException(lang('Relations.invalidWithout'));
		}
		
		if (is_string($tables))
		{
			$tables = [$tables];
		}
	
		$this->tmpWithout = array_merge($this->without, $tables);
		
		return $this;
	}

	//--------------------------------------------------------------------
	// FINDERS EXTENSIONS
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
			return $rows;
		}

		// Likewise for empty singletons
		if (count($rows) == 1 && reset($rows) == null)
		{
			unset($this->tmpWith, $this->tmpWith);
			return $rows;
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
		
		// Make sure the schema is loaded
		$this->ensureSchema();
		
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
				foreach ($relations as $tableName => $related)
				{
					// Collapse singleton relationships to the object itself
					if (self::$schema->tables->{$this->table}->relations->{$tableName}->singleton)
					{
						$key        = singular($tableName);
						$object     = reset($related[$id]) ?? null;
						$item[$key] = $object;
					}
					else
					{
						$item[$tableName] = $related[$id] ?? [];
					}
				}
			}
			// Handle object return types
			else
			{
				$id = $item->{$this->primaryKey};
				
				// Inject related items
				foreach ($relations as $tableName => $related)
				{
					// Collapse singleton relationships to the object itself
					if (self::$schema->tables->{$this->table}->relations->{$tableName}->singleton)
					{
						$property        = singular($tableName);
						$object          = reset($related[$id]) ?? null;
						$item->$property = $object;
					}
					else
					{
						$item->$tableName = $related[$id] ?? [];
					}
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
		// Make sure the schema knows the related table
		if (! isset(self::$schema->tables->{$tableName}))
		{			
			throw RelationsException::forUnknownTable($tableName);
		}
		// Fetch the related table for easy access
		$table = self::$schema->tables->{$tableName};

		// Make sure the related table is actually related to this model's table
		if (! isset(self::$schema->tables->{$this->table}->relations->{$table->name}))
		{
			throw RelationsException::forUnknownRelation($this->table, $table->name);
		}
		// Fetch the relation for easy access
		$relation = self::$schema->tables->{$this->table}->relations->{$table->name};

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
				if (self::$config->allowNesting)
				{
					// Add this table to the "without" list
					$this->without($this->table);
					$builder->without($this->tmpWithout);
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
			$builder = $this->db->table($table->name);
			$returnType = self::$config->defaultReturnType;
		}

		// Define the returns
		$builder->select("{$table->name}.*");
		
		// Handle each relationship type differently
		switch ($relation->type)
		{
			// hasMany is the easiest because it doesn't need joins
			case 'hasMany':
				// Grab the first (should be only) pivot: [$table->name, $table->primaryKey, $this->table, foreignKey]
				$pivot = reset($relation->pivots);
				$originating = "{$pivot[2]}.{$pivot[3]}";
			break;
			
			// belongsTo joins this model's table directly
			case 'belongsTo':
				// belongsTo is the only relationship where the originating ID is not available in the pivot table
				// so we get it from this model's table
				$originating = "{$this->table}.{$this->primaryKey}";
				
				// Grab the first (should be only) pivot: [$this->table, foreignKey, $table->name, $table->primaryKey]
				$pivot = reset($relation->pivots);
				
				// Join this model's table (for ID filtering)
				$builder->join($pivot[0], "{$pivot[0]}.{$pivot[1]} = {$pivot[2]}.{$pivot[3]}");
			break;
			
			// manyToMany and manyThrough navigate the pivots stopping at the join table
			default:
				// Determine originating from the first pivot
				$pivot = array_shift($relation->pivots); // [$this->table, $this->primaryKey, pivotTable, foreignKey]
				$originating = "{$pivot[2]}.{$pivot[3]}";

				// Navigate the remaining pivots to generate join statements
				foreach ($relation->pivots as $pivot)
				{
					$builder->join($pivot[0], "{$pivot[0]}.{$pivot[1]} = {$pivot[2]}.{$pivot[3]}");
				}
		}
		
		// Filter on the requested IDs
		$builder->select("{$originating} AS originating_id");
		$builder->whereIn("{$originating}", $ids);
		
		// If the model is available use it to get the result
		// Also triggers model's afterFind
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

		// Reindex the results by the originating ID (this model's primary key)
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
	 * If multiple rows have the same primary (e.g. a join) it returns the originals
	 *
	 * @param array  $rows  Array of rows from the finder
	 *
	 * @return array
	 */
	protected function simpleReindex($rows): array
	{
		if (empty($rows))
		{
			return [];
		}
		
		// Reindex $rows by this model's primary key and inject related items
		$return = [];
		foreach ($rows as $item)
		{
			// Handle array return types
			if (is_array($item))
			{
				$id = $item[$this->primaryKey] ?? null;
			}
			// Handle object return types
			else
			{
				$id = $item->{$this->primaryKey} ?? null;
			}
			
			// If no primary key or an entry already existed then return it as is
			// Probably the former is custom select() and the latter is a join()
			if (empty($id) || isset($return[$id]))
			{
				return $rows;
			}
			$return[$id] = $item;
		}
		
		return $return;
	}
	
	/**
	 * Ensures there is a schema to work from. Only called when necessary to prevent
	 * repeat calls or recursion loops.
	 */
	protected function ensureSchema()
	{
		if (self::$schema)
		{
			return;
		}
		
		// Load the schema service & config
		$schemas = Services::schemas();
		$config  = config('Schemas');
		if (empty($schemas))
		{
			throw new \RuntimeException(lang('Relations.noSchemas'));
		}
		
		// Check for a schema from the cache
		$schema = $schemas->import('cache')->get();
		if (is_null($schema))
		{
			// Generate the schema from the default handlers and save it to the cache
			$schemas->import($config->defaultHandlers)->export('cache');
			$schema = $schemas->get();
		}
		
		self::$schema = $schema;
	}
}
