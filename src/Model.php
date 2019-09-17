<?php namespace Tatter\Prefetch;

use CodeIgniter\Config\Services;
use Tatter\Relations\Exceptions\RelationsException;

class Model extends \CodeIgniter\Model
{
	// Static instance of the schema
	protected static $schema;
	
	// Array of related tables to fetch from when using finders
	protected $with;
	
	// Call CI model constructor then load the schema and register afterFind
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
        parent::__construct($db, $validation);
		
		// First model loads the schema
		if (is_null(self::$schema))
		{
			$schemas = Services::schemas();
			
			// Check for a schema from cache
			$schema = $this->schemas->import('cache')->get();
			if (is_null($schema))
			{
				// Generate the schema and save it to the cache
				$schemas->import('database', 'model')->export('cache');
				$schema = $schemas->get();
			}
			
			// Keep a copy of the schema
			self::$schema = $schema;
		}
		
		// Register our event
		$this->afterFind[] = 'findRelations';
	}
	
	/**
	 * Adds related tables to load along with the next finder.
	 *
	 * @param mixed   $with       Table name or array of table names
	 * @param bool    $overwrite  Whether to merge with existing table 'with' list
	 *
	 * @return $this
	 */
	public function with($with, bool $overwrite = false)
	{
		if (! is_array($with))
		{
			$with = [$with];
		}
	
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
		// Get data from parent Model as usual
		$data = parent::find($id);
		
		// If no matches then reset tmpWith and quit
		if (empty($data))
		{
			unset($this->tmpWith);
			return $data;
		}
		
		// If no tmpWith was set then use this model's default
		if (! isset($this->tmpWith))
		{
			$this->tmpWith = $this->with;
		}
		
		// If tmpWith is still empty then reset and quit
		if (empty($this->tmpWith))
		{
			unset($this->tmpWith);
			return $data;
		}
				
		// Check for a singleton
		if (is_numeric($id) || is_string($id))
		{
			$ids = [$id];
		}
		// For an array of items harvest the IDs
		else
		{
			$ids = array_column($data, $this->primaryKey);
		}
		
		// Find the relations for each table
		$relations = [];
		foreach ($this->tmpWith as $tableName)
		{
			$relations[$tableName] = $this->findRelated($tableName, $ids);
		}
		
		
			$ids = [];
			$array = [];
			foreach ($data as $item)
			{
				if (is_array($item))
				{
					$ids[] = $item[$this->primaryKey];
					$array[$item[$this->primaryKey]] = $item;
				}
				else
				{
					$ids[] = $item->{$this->primaryKey};
					$array[$item{$this->primaryKey}] = $item;
				}
			}
		
		// Reset tmpWith
		$this->tmpWith = [];
		
		return $return;
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
		$data = parent::find($id);
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
		$data = parent::find($id);	
	}

	/**
	 * Uses the Schema to load related items
	 *
	 * @param string $tableName  Name of the table for related items
	 * @param array  $ids  Array of primary keys
	 *
	 * @return array
	 */
	public function findRelated($tableName, $ids): array
	{
		// Make sure the schema knows this table
		if (! isset($this->schema->tables->{$tableName}))
		{			
			throw RelationsException::forUnknownTable($tableName);
		}
		// Fetch the related table for easy access
		$table = $this->schema->tables->{$this->table};
		
		// Make sure the model table is related to the requested table
		if (! isset($this->schema->tables->{$this->table}->relations->{$tableName}))
		{
			throw RelationsException::forUnknownRelation($this->table, $tableName);
		}
		// Fetch the relation for easy access
		$relation = $this->schema->tables->{$this->table}->relations->{$tableName};
		
		// Verify pivots
		if (empty($relation->pivots))
		{
			throw SchemasException::forMissingPivots($this->table, $tableName);			
		}
		
		// Check for a known model for the target table
		if (isset($table->model))
		{
			// Grab an instance of the model to use as the builder
			$builder    = new ($table->model)();
			$returnType = $builder->returnType;
		}
		// No model - use a generic builder
		else
		{
			$builder = $this->db->table($tableName);
			$returnType = config('Relations')->defaultReturnType;
		}
		
		// Define the returns
		$builder->select("{$tableName}.*");

		// We also need the model table's pivot ID in the return
		$pivot = reset($relation->pivots); // [table1, localKey, foreignKey]
		$builder->select("{$pivot[0].{$pivot[2]} AS originating_id");
		
		// Navigate the pivots to generate join statements
		$currentTable = $this->table;
		foreach ($relation->pivots as $pivot)
		{
			$builder->join($pivot[0], "{$currentTable}.{$pivot[1]} = {$pivot[0]}.{$pivot2}");
			$currentTable = $pivot[0];
		}
		
		// If the model is available use it to get result
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
		unset($table);
		unset($relation);
		unset($builder);
		
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
}
