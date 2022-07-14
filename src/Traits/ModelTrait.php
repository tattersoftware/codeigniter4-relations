<?php

namespace Tatter\Relations\Traits;

use RuntimeException;
use Tatter\Schemas\Structures\Schema;

trait ModelTrait
{
    use BaseTrait;

    /**
     * Whether to reindex results by the primary key
     *
     * @var bool
     */
    protected $reindex = true;

    /**
     * Add related tables to load along with the next finder.
     *
     * @param mixed $with      Table name, array of table names, or false (to disable)
     * @param bool  $overwrite Whether to merge with existing table 'with' list
     *
     * @return $this
     */
    public function with($with, bool $overwrite = false)
    {
        // Check for a request to disable
        if ($with === false) {
            $this->tmpWith = [];

            return $this;
        }

        // Force a single table name into an array
        if (! is_array($with)) {
            $with = [$with];
        }

        // Option to override this model's pre-seeded value
        $this->tmpWith = $overwrite ? $with : array_merge($this->getWith(), $with);

        return $this;
    }

    /**
     * Blocks specified tables from being loaded as relations with the next finder.
     * Used mostly to prevent nesting loops.
     *
     * @param string|string[] $tables Table name or array of table names
     *
     * @return $this
     */
    public function without($tables)
    {
        // @phpstan-ignore-next-line
        if (! is_string($tables) && ! is_array($tables)) {
            throw new RuntimeException(lang('Relations.invalidWithout'));
        }

        if (is_string($tables)) {
            $tables = [$tables];
        }

        $this->tmpWithout = array_merge($this->getWithout(), $tables);

        return $this;
    }

    /**
     * Return $with
     */
    protected function getWith(): array
    {
        // Ensure $this->with is set at all
        if (empty($this->with)) {
            $this->with = [];
        }

        // Force a single table name into an array
        if (! is_array($this->with)) {
            $this->with = [$this->with];
        }

        return $this->with;
    }

    /**
     * Return $withOut
     */
    protected function getWithout(): array
    {
        // Ensure $this->without is set at all
        if (empty($this->without)) {
            $this->without = [];
        }

        // Force a single table name into an array
        if (! is_array($this->without)) {
            $this->without = [$this->without];
        }

        return $this->without;
    }

    /**
     * Reset per-query variables.
     *
     * @return $this
     */
    public function resetTmp()
    {
        unset($this->tmpWith, $this->tmpWithout, $this->tmpReindex);

        return $this;
    }

    /**
     * Enable/disable result reindexing.
     *
     * @return $this
     */
    public function reindex(bool $bool = true)
    {
        $this->reindex = $bool;

        return $this;
    }

    /**
     * Intercept join requests to disable reindexing.
     *
     * @return $this
     */
    public function join(...$params)
    {
        $this->tmpReindex = false;

        // Pass through to the builder
        $this->builder()->join(...$params);

        return $this;
    }

    //--------------------------------------------------------------------
    // FINDERS EXTENSIONS
    //--------------------------------------------------------------------

    /**
     * Fetches the row of database from $this->table with a primary key
     * matching $id.
     *
     * @param array|mixed|null $id One primary key or an array of primary keys
     *
     * @return array|object|null The resulting row of data, or null.
     */
    public function find($id = null)
    {
        // Get data from the framework model as usual
        $data = parent::find($id);

        // For singletons, wrap them as a one-item array and then unwrap on return
        if (is_numeric($id) || is_string($id)) {
            $data = $this->addRelations([$data]);

            return reset($data);
        }

        return $this->addRelations($data);
    }

    //--------------------------------------------------------------------
    /**
     * Works with the current Query Builder instance to return
     * all results, while optionally limiting them.
     *
     * @return array|null
     */
    public function findAll(int $limit = 0, int $offset = 0)
    {
        $data = parent::findAll($limit, $offset);

        return $this->addRelations($data);
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
        $data = $this->addRelations([$data]);

        return reset($data);
    }

    /**
     * Intercepts data from a finder and injects related items
     *
     * @param array $rows Array of rows from the finder
     *
     * @return array
     */
    protected function addRelations($rows): ?array
    {
        // If there were no matches then reset per-query data and quit
        if (empty($rows)) {
            $this->resetTmp();

            return $rows;
        }

        // Likewise for empty singletons
        if (count($rows) === 1 && reset($rows) === null) {
            $this->resetTmp();

            return $rows;
        }

        // If no tmpWith was set then use this model's default
        if (! isset($this->tmpWith)) {
            $this->tmpWith = $this->getWith();
        }
        // If no tmpWithout was set then use this model's default
        if (! isset($this->tmpWithout)) {
            $this->tmpWithout = $this->getWithout();
        }
        // If no tmpReindex was set then use this model's default
        if (! isset($this->tmpReindex)) {
            $this->tmpReindex = $this->reindex;
        }

        // Remove any blocked tables from the request
        $this->tmpWith = array_diff($this->tmpWith, $this->tmpWithout);

        // If tmpWith ends up empty then reset and quit
        if (empty($this->tmpWith)) {
            $rows = $this->tmpReindex ? $this->simpleReindex($rows) : $rows;
            $this->resetTmp();

            return $rows;
        }

        // Harvest the IDs that want relations
        $ids = array_column($rows, $this->primaryKey);

        // Get the schema
        $schema = $this->_schema();

        // Find the relations for each table
        $relations = $singletons = [];

        foreach ($this->tmpWith as $tableName) {
            // Check for singletons
            $relation               = $this->_getRelationship($tableName);
            $singletons[$tableName] = $relation->singleton ? singular($tableName) : false;

            $relations[$tableName] = $this->_getRelations($tableName, $ids);
        }
        unset($schema);

        // Inject related items back into the rows
        $return = [];

        foreach ($rows as $item) {
            $id = is_array($item) ? $item[$this->primaryKey] : $item->{$this->primaryKey};

            // Inject related items
            foreach ($relations as $tableName => $related) {
                // Assign singletons to a property named for the singular table
                if ($name = $singletons[$tableName]) {
                    if (is_array($item)) {
                        $item[$name] = $related[$id] ?? null;
                    } else {
                        $item->{$name} = $related[$id] ?? null;
                    }
                } elseif (is_array($item)) {
                    $item[$tableName] = $related[$id] ?? [];
                } else {
                    $item->{$tableName} = $related[$id] ?? [];
                }
            }

            if ($this->tmpReindex) {
                $return[$id] = $item;
            } else {
                $return[] = $item;
            }
        }

        // Clear old data and reset per-query properties
        unset($rows);
        $this->resetTmp();

        return $return;
    }

    /**
     * Reindexes $rows from a finder by their primary key
     * Mostly used for consistent return format when no relations are requested
     * If multiple rows have the same primary (e.g. a join) it returns the originals
     *
     * @param array $rows Array of rows from the finder
     */
    public function simpleReindex($rows): array
    {
        if (empty($rows)) {
            return [];
        }

        // Reindex $rows by this model's primary key and inject related items
        $return = [];

        foreach ($rows as $item) {
            // Handle array return types
            $id = is_array($item) ? $item[$this->primaryKey] ?? null : $item->{$this->primaryKey} ?? null;

            // If no primary key or an entry already existed then return it as is
            // Probably the former is custom select() and the latter is a join()
            if (empty($id) || isset($return[$id])) {
                return $rows;
            }
            $return[$id] = $item;
        }

        return $return;
    }
}
