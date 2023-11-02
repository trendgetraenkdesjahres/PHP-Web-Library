<?php

namespace DataStorage;

require_once "DatabaseTable.php";
require_once "FileTable.php";

/**
 * DataStorageTableInterface defines the contract for DataStorageTable classes.
 */
interface DataStorageTableInterface
{
    public function get_name(): string;
    public function get_row(int $id): array;
    public function get_cell_where(string $return_column, string ...$where_condition): mixed;
    public function get_any_row_where(?string ...$where_condition): array;
    public function get_related_cell(
        int $id,
        DataStorageTable $related_table,
        string $related_return_column,
    ): mixed;
    public function get_related_cell_where(
        DataStorageTable $related_table,
        string $related_return_column,
        string ...$where_condition,
    ): mixed;

    // public function get_joined_related_cell_where();

    public function add_row(array $key_value_pairs): DataStorageTable;

    public function set_cell(int $id, string $column, mixed $value): DataStorageTable;
    public function set_cell_where(string $column, mixed $value, $where_condition): DataStorageTable;

    public function delete_row(int $id): DataStorageTable;
    public function delete_row_where($where_condition): DataStorageTable;
}

class DataStorageTable
{
    public function __construct(public string $name)
    {
    }

    /**
     * Get the name of the table.
     *
     * @return string The table name.
     */
    public function get_name(): string
    {
        return $this->name;
    }
}

/**
 * TableColumn represents a table column with its properties.
 */
class TableColumn
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
