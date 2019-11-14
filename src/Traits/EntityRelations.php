<?php namespace Tatter\Relations\Traits;

trait EntityRelations
{
	use SchemaLoader;

	/**
	 * Intercept undefined methods and check them against known relations
	 *
	 * @param string $name      The name of the missing method
	 * @param array $arguments  Any arguments passed; expects one: empty, singleton row, array of rows, single key, array of keys
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

		// Format target as a valid table reference
		if (! function_exists('plural'))
		{
			helper('inflector');
		}
		$table = plural(strtolower($target));
		
		// Parse the arguments into an array of primary keys from $table
		$args = empty($arguments) ? null : $this->_parseArgument($table, reset($arguments));

		// At this point we have a match - pass to the appropriate function
		$method = '_' . $verb;
		$this->$method($table, $args);
	}

	/**
	 * Parse the argument into an array of keys (or null)
	 *
	 * @param array $argument  The argument passed via __call()
	 *
	 * @return array|null  Array of primary keys to the target table
	 */
	protected function _parseArgument($table, $argument): ?array
	{		
		// Check for an empty argument: false, [], null
		if (empty($argument)
		{
			return null;
		}
		
		// Check for a single key
		if (is_string($argument) || is_int($argument))
		{
			return [$argument];
		}
		
		// If it is an array we need to determine if it is a singleton row
		if (is_array($argument))
		{
			$test = reset($argument);
			
			// If the first item is a potential key then check array indexes for sequential numeric versus field names
			if (is_string($test) || is_int($test))
			{
				$test = key($argument);
				
				// If the index is 0 then this is a sequential array of keys - exactly what we already wanted
				if ($test === 0)
				{
					return $argument;
				}
				
				// At this point we know $argument is a singleton row - repackage into an array
				$argument = [$argument];
			}
		}

		// If it is a singleton object then repacakge it into an array
		elseif (is_object($argument))
		{
			$argument = [$argument];
		}

		// Not empty nor an int, string, array, or object (bool true maybe?) - give up
		else
		{
			throw new \InvalidArgumentException('Entity relation functions cannot accept arguments of type ' . gettype($argument));
		}
		
		// At this point we have an array of array rows or objects and need to determine their primary key
		
		

	}

	/**
	 * Check if this entity has specific relation(s) in the given table
	 *
	 * @param string $name      The name of the missing method
	 * @param array $arguments  Any arguments passed
	 *
	 * @return mixed
	 */
	protected function _has($table, $arguments)
	{
		
		
		// Check for existing attribute
		if (isset($this->attributes[$table]))
		{
			foreach ($this->attributes['options'] as $option)
			{
				if ($option->id == $optionId)
				{
					return true;
				}
			}
			
			return false;
		}

		// Get the schema
		$schema = $this->loadSchema();
		
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
