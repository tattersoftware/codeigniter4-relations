<?php

namespace Tatter\Relations\Traits;

use RuntimeException;
use Tatter\Relations\Exceptions\RelationsException;
use Tatter\Schemas\Structures\Relation;
use Tatter\Schemas\Structures\Schema;
use Tatter\Schemas\Structures\Table;

trait BaseTrait
{
    /**
     * Uses the schema to determine this class's relationship to a table
     *
     * @param string $tableName Name of the target table
     */
    public function _getRelationship($tableName): Relation
    {
        $this->_verifyRelatable();

        // Get the schema
        $schema = $this->_schema();

        // Make sure the schema knows the target table
        if (! isset($schema->tables->{$tableName})) {
            throw RelationsException::forUnknownTable($tableName);
        }

        // Fetch the target table
        $table = $schema->tables->{$tableName};

        // Make sure the tables are actually related
        if (! isset($schema->tables->{$this->table}->relations->{$table->name})) {
            throw RelationsException::forUnknownRelation($this->table, $table->name);
        }

        // Get the relation
        $relation = $schema->tables->{$this->table}->relations->{$table->name};

        // Verify that pivots are defined
        if (empty($relation->pivots)) {
            throw RelationsException::forMissingPivots($this->table, $tableName);
        }

        return $relation;
    }

    /**
     * Uses the schema to load related items
     *
     * @param string     $tableName Name of the table for related items
     * @param array|null $ids       Filter for this class's primary keys
     *
     * @return array [$id => [$relatedItems]], or [$id => $relatedItem] for singletons
     */
    public function _getRelations($tableName, $ids = null): array
    {
        $this->_verifyRelatable();

        // Fetch the target table
        /** @var Table $table */
        $table = $this->_schema()->tables->{$tableName};

        // Get the relationship
        $relation = $this->_getRelationship($tableName);

        // Get the config
        $config = config('Relations');

        // Check for a known model for the target table
        if (isset($table->model)) {
            // Grab an instance of the model to use as the builder
            $class      = $table->model;
            $builder    = new $class();
            $returnType = $builder->returnType;
            unset($class);

            // If this was called from a model then check for another Relations model (to prevent nesting loops)
            if (method_exists($builder, '_getRelations')) {
                // Don't reindex (we'll do our own below)
                $builder->reindex(false);

                // If nesting is allowed we need to disable the target table
                if ($config->allowNesting) {
                    // Add the target table to the "without" list
                    $without   = $this->tmpWithout ?? [];
                    $without[] = $table->name;
                    $builder->without($without);
                }
                // Otherwise turn off relation loading on returned relations
                else {
                    $builder->with(false);
                }
            }
        }

        // No model - use a generic builder
        else {
            $builder    = isset($this->db) ? $this->db->table($table->name) : db_connect()->table($table->name);
            $returnType = $config->defaultReturnType;
        }

        // Define the returns
        $builder->select("{$table->name}.*");

        // Handle each relationship type differently
        switch ($relation->type) {
            // hasMany is the easiest because it doesn't need joins
            case 'hasMany':
                // Grab the first (should be only) pivot: [$table->name, $table->primaryKey, $this->table, foreignKey]
                $pivot       = reset($relation->pivots);
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
                $pivot       = reset($relation->pivots); // [$this->table, $this->primaryKey, pivotTable, foreignKey]
                $originating = "{$pivot[2]}.{$pivot[3]}";

                // Navigate the remaining pivots to generate join statements
                while ($pivot = next($relation->pivots)) {
                    $builder->join($pivot[0], "{$pivot[0]}.{$pivot[1]} = {$pivot[2]}.{$pivot[3]}");
                }
        }

        $builder->select("{$originating} AS originating_id");

        // Entities always filter by themselves
        if (! empty($this->attributes[$this->primaryKey])) {
            $builder->where("{$originating}", $this->attributes[$this->primaryKey]);
        }
        // Check for an explicit filter request
        elseif (! empty($ids)) {
            $builder->whereIn("{$originating}", $ids);
        }

        // If the model is available then use it to get the result
        // (Bonus: triggers model's afterFind)
        if (isset($table->model)) {
            // Check if this table should include soft deletes
            if (isset($this->withDeletedRelations) && in_array($table->name, $this->withDeletedRelations, true)) {
                $builder->withDeleted();
            }

            $results = $builder->find();
        } else {
            $results = $builder->get()->getResult();
        }

        // Clean up
        unset($table, $builder);

        // Reindex the results by the originating ID (this table's primary key)
        $return = [];

        foreach ($results as $row) {
            if ($returnType === 'array') {
                $originatingId = $row['originating_id'];
                unset($row['originating_id']);
            } else {
                $originatingId = $row->originating_id;
                unset($row->originating_id);
            }

            // Singleton return one row set, others append to an array
            if ($relation->singleton) {
                $return[$originatingId] = $row;
            } else {
                $return[$originatingId][] = $row;
            }
        }

        return $return;
    }

    /**
     * Preps the Schemas service with a current schema to share across this library and returns it.
     */
    protected function _schema(): Schema
    {
        $this->_verifyRelatable();

        // Load the Schemas service
        $schemas = service('schemas');

        if (empty($schemas)) {
            throw new RuntimeException(lang('Relations.noSchemas'));
        }

        // Check for a schema using the defaults
        $schema = $schemas->get();

        if (null === $schema) {
            // Try reading an archived schema
            $schema = $schemas->read()->get();

            if (null === $schema) {
                // Give up
                throw new RuntimeException(lang('Relations.noSchemas'));
            }
        }

        return $schema;
    }

    /**
     * Ensure this class has everything it needs to use Relations
     */
    protected function _verifyRelatable()
    {
        if (empty($this->table)) {
            throw RelationsException::forMissingProperty(self::class, 'table');
        }

        if (empty($this->primaryKey)) {
            throw RelationsException::forMissingProperty(self::class, 'primaryKey');
        }

        // Make sure we have the inflector helper
        if (! function_exists('plural')) {
            helper('inflector');
        }
    }
}
