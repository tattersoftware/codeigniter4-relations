<?php namespace Tatter\Relations\Traits;

use Config\Services;
use Tatter\Schemas\Structures\Schema;

trait SchemaLoader
{
	/**
	 * Ensures the Schemas service has a current schema to share across this library.
	 *
	 * @return Schema
	 */
	protected function loadSchema(): Schema
	{
		// Load the Schemas service
		$schemas = Services::schemas();

		if (empty($schemas))
		{
			throw new \RuntimeException(lang('Relations.noSchemas'));
		}
		
		// Check for a schema using the defaults
		$schema = $schemas->get();

		if (is_null($schema))
		{
			// Try reading an archived schema
			$schema = $schemas->read()->get();

			if (is_null($schema))
			{
				// Give up
				throw new \RuntimeException(lang('Relations.noSchemas'));
			}
		}
		
		return $schema;
	}
}
