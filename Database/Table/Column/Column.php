<?php

namespace PHP_Library\Database\Table\Column;

/**
 * TableColumn represents a table column with its properties.
 */
class Column
{
    public string $name;

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
        $this->name = trim($name);
    }
}
