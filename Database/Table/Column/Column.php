<?php

namespace PHP_Library\Database\Table\Column;

use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;
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
class Column
{
    /**
     * The name of the column.
     *
     * @var string
     */
    public string $name;


    /**
     * The type of the column.
     *
     * @var ColumnType
     */
    protected ColumnType $type;

    /**
     * Indicates if the column has an auto-increment property.
     *
     * @var bool
     */
    public static bool $auto_increment = false;

    /**
     * Indicates if the column is a primary key.
     *
     * @var bool
     */
    public static bool $is_primary_key = false;

    /**
     * Constructor for the Column class.
     *
     * @param string $name The column name (validated against SQL syntax rules).
     * @param string $type The data type of the column (default: 'string').
     * @param int|null $length The maximum length of the column (optional).
     * @param bool $nullable Whether the column allows NULL values (default: false).
     * @param bool $timestamp Whether the column is of type timestamp (default: false).
     *
     * @throws SQLanguageError If the column name does not pass SQL syntax validation.
     */
    public function __construct(
        string $name,
        string|ColumnType $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public bool $timestamp = false
    ) {
        if (! SyntaxCheck::is_field_name($name)) {
            throw new SQLanguageError("{$name} is not a column name.");
        }
        if (is_string($type)) {
            try {
                $type = ColumnType::create_from_string($type);
            } catch (\Error $e) {
                throw new Error("Can not create Column of type $type");
            }
        }
        $this->type = $type;
        $this->name = trim($name);
    }

    public function __get($property): string
    {
        if ($property !== 'type') {
            return null;
        }
        switch (Database::get_type()) {
            case 'FileDatabase':
                return $this->type->get_php_type();
            case 'SQLDatabase':
                return $this->type->get_sql_type();
            default:
                throw new Error("No Database Type");
        }
    }
}
