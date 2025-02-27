<?php

namespace PHP_Library\Database\Table\Column;

use PHP_Library\Database\Table\Column\ColumnType\Boolean;
use PHP_Library\Database\Table\Column\ColumnType\FloatingPoint;
use PHP_Library\Database\Table\Column\ColumnType\Integer;
use PHP_Library\Database\Table\Column\ColumnType\Text;
use PHP_Library\Error\Error;

/**
 * Class Column
 *
 * Represents a database table column with attributes like name, type, length, and constraints.
 *
 * Dependencies:
 * - SyntaxCheck: For validating column names against SQL syntax rules.
 * - SQLanguageError: For handling SQL syntax-related errors.
 */
abstract class ColumnType
{
    abstract public function get_sql_type(): string;
    abstract public function get_php_type(): string;
    abstract public function __construct();

    public static function create_from_string(string $type = 'string'): static
    {
        switch ($type) {
            case 'string':
                return new Text();
            case 'int':
                return new Integer();
            case 'float':
                return new FloatingPoint();
            case 'bool':
                return new Boolean();
            default:
                throw new Error("Can not create type by string. Type '{$type}' unknown.");
        }
    }
}
