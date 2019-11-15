<?php namespace Tatter\Relations\Traits;

use Tatter\Relations\Exceptions\RelationsException;

trait EntityRelations
{
	use SchemaLoader;

	/**
	 * Validate Relatable then call the framework Entity constructor.
	 *
	 * @param array|null $data
	 */
	public function __construct(array $data = null)
	{
		$this->ensureRelatable();

        parent::__construct($data);
	}

	/**
	 * Check "gets" unmatched from the framework entity for known relations
	 *
	 * @param string $key  The name of the requested property, i.e. table to check for relations
	 *
	 * @return mixed|null  Return is determined by relation type, see _getRelations()
	 */
	public __get(string $key)
	{
		// First check the framework's version
		$result = parent::__get($key);
		
		if ($result !== null)
		{
			return $result;
		}

		// Get the schema
		$schema = $this->loadSchema();

		// Convert the key to table format
		$tableName = plural(strtolower($key));
		
		// Check for a matching table
		if ($schema->tables->$tableName)
		{
			return $this->_getRelations($tableName);
		}
		
		return null;
	}

	/**
	 * Intercept undefined methods and check them against known relations
	 *
	 * @param string $name  The name of the missing method
	 * @param array  $arguments  Any arguments passed. Expects only one: an array of keys or null
	 *
	 * @return mixed
	 */
	public __call(string $name, array $arguments)
	{
		$verbs = ['has', 'get', 'add', 'remove', 'save'];

		// Parse the name to check for supported relation verbs
		$verb = false;
		foreach ($verbs as $test)
		{
			if (strpos($name, $test) === 0)
			{
				$verb = $test;
				break;
			}
		}

		// If no verb matched then this is an unrelated call
		if (empty($verb))
		{		
			throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
		}
		
		// Trim the verb and check what is left
		$target = substr($name, strlen($verb));
		
		// Check for an uppercase character next
		// https://stackoverflow.com/questions/2814880/how-to-check-if-letter-is-upper-or-lower-in-php
		if (! preg_match('~^\p{Lu}~u', $target))
		{
			throw new \BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
		}

		// Validate argument count
		if (count($arguments) > 1)
		{
			throw new \ArgumentCountError(sprintf('Too many arguments to function %s::%s, %s passed and at most 1 expected', static::class, $name, count($arguments)));
		}

		// Make sure this entity's table is set
		if (empty($this->table))
		{
			throw RelationsException::forMissingEntityTable(get_class());
		}
		
		// Format target as a valid table reference
		if (! function_exists('plural'))
		{
			helper('inflector');
		}
		$tableName = plural(strtolower($target));
		
		// Flatten the arguments to just the keys
		$keys = reset($arguments);
		if (empty($keys))
		{
			$keys = null;
		}
		// Wrap singletons in an array
		elseif (! is_array($keys))
		{
			$keys = [$keys];
		}

		// Pass to the appropriate function
		$method = '_' . $verb;
		$this->$method($tableName, $keys);
	}

	/**
	 * Get related items from a known related table
	 * Note that __get() has already checked $this->attributes for $tableName
	 *
	 * @param string $tableName  The name of the table to check for relations
	 * @param string $keysOnly   Whether to return the entire rows or just primary keys
	 *
	 * @return mixed  Function return is determined by the relation type:
	 *                              array of $returnTypes (hasMany, manyToMany)
 	 *                              single $returnType (belongsTo, hasOne)
	 */
	public relations(string $tableName, $keysOnly = false)
	{
		$id = $this->attributes[$this->primaryKey];

		// Use the SchemaLoader trait to find related items
		$items = $this->findRelated($tableName, [$id]);
		
		// Save them for future use
		$this->attributes[$tableName] = $items[$id];
		
		// WIP - needs to collapse singletons!

		return $keysOnly ? $items[$id] : array_column($items[$id], $this->primaryKey);
	}

	/**
	 * Check if this entity has specific relation(s) in a table
	 *
	 * @param string $tableName  The name of the target table
	 * @param array|null $keys  Primary keys to match, or null for "any"
	 *
	 * @return mixed
	 */
	protected function _hasRelations($tableName, $keys = null)
	{
		// Get the primary key from the schema
		$schema  = $this->loadSchema();
		$keyName = $schema->tables->$tableName->primaryKey;
		
		// Check for existing relations (probably loaded by the model)
		if (isset($this->attributes[$tableName]))
		{
			// If no keys were requested then check for at least one entity
			if (empty($keys))
			{
				return ! empty($this->attributes[$tableName]);
			}
			
			// Otherwise count how many of the requested keys are matched
			$matched = 0;
			foreach ($this->attributes[$tableName] as $entity)
			{
				$key = is_array($entity) ? $entity[$keyName] : $entity->$keyName;

				if (in_array($key, $keys))
				{
					$matched++;
					if ($matched >= count($keys))
					{
						return true;
					}
				}
			}
			
			// Didn't match all of the passed keys
			return false;
		}
		
		// Check the database
		$builder = db_connect()->table('jobs_options');
		
		return (bool)$builder
			->where('job_id', $this->attributes['id'])
			->where('option_id', $optionId)
			->countAllResults();
	}

	// Set options for a job in the database
	protected function updateOptions($optionIds)
	{
		$builder = db_connect()->table('jobs_options');
		
		// Clear existing options
		$builder->where('job_id', $this->attributes['id'])->delete();
		unset($this->attributes['options']);

		// If there are no IDs then finish
		if (empty($optionIds))
		{
			return;
		}
		
		// Add back any selected options
		$rows = [];
		foreach ($optionIds as $optionId)
		{
			$rows[] = [
				'job_id'    => $this->attributes['id'],
				'option_id' => $optionId,
			];
		}

		$builder->insertBatch($rows);
	}	
}
