<?php

namespace PHP_Library\Database\Table\Column;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * TableColumn represents a table column with its properties.
 */
class Column
{
    public string $name;
    public static bool $auto_increment = false;
    public static bool $is_primary_key = false;

    /**
     * Constructor for TableColumn.
     *
     * @param string $name The column name.
     * @param string $type The data type of the column.
     * @param int|null $length The maximum length of the column (if applicable).
     * @param bool $nullable Whether the column is nullable.
     * @param bool $timestamp Whether the column is a timestamp.
     */
    public function __construct(
        string $name,
        public string $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public bool $timestamp = false
    ) {
        if (! SyntaxCheck::is_field_name($name)) {
            throw new DatabaseError("{$name} is not a column name.");
        }
        $this->name = trim($name);
        // TODO apply sql lang check on type!!
    }
}
