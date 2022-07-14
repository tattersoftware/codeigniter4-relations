<?php

namespace Tatter\Relations\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class RelationsException extends RuntimeException implements ExceptionInterface
{
    public static function forUnknownTable($tableName)
    {
        return new static(lang('Relations.unknownTable', [$tableName]));
    }

    public static function forUnknownRelation($table1, $table2)
    {
        return new static(lang('Relations.unknownRelation', [$table1, $table2]));
    }

    public static function forMissingPivots($table1, $table2)
    {
        return new static(lang('Relations.missingPivots', [$table1, $table2]));
    }

    public static function forMissingProperty($class, $property)
    {
        return new static(lang('Relations.missingProperty', [$class, $property]));
    }

    public static function forNotRelatable($class)
    {
        return new static(lang('Relations.notRelatable', [$class]));
    }
}
