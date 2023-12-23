<?php

namespace DataStorage;

require_once "DatabaseTable.php";
require_once "FileTable.php";

/**
 * DataStorageTableInterface defines the contract for DataStorageTable classes.
 */
interface DataStorageTableInterface
{
    /**
     * Get the name of the table.
     *
     * @return string The name.
     */
    public function get_name(): string;

    /**
     * Get a row from the table by its ID.
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    public function get_row(int $id): array;

    /**
     * Get a cell from the table based on WHERE conditions. If there multiple matches, it will just return the first one.
     *
     * @param string $return_column The column to retrieve.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The cell value.
     */
    public function get_cell_where(string $return_column, string ...$where_condition);

    /**
     * Get any rows from the table based on WHERE conditions.
     *
     * @param string ...$where_condition The WHERE conditions.
     * @return array An array of rows.
     */
    public function get_any_row_where(?string ...$where_condition): array;

    /**
     * Get a related cell from another table by ID.
     *
     * @param int $id The ID to match in the related table.
     * @param DataStorageTable $related_table The related table.
     * @param string $related_return_column The column to retrieve from the related table.
     * @return mixed The related cell value.
     */
    public function get_related_cell(
        int $id,
        DataStorageTable $related_table,
        string $related_return_column,
    ): mixed;

    /**
     * Get a related cell from another table based on WHERE conditions.
     *
     * @param DataStorageTable $related_table The related table.
     * @param string $related_return_column The column to retrieve from the related table.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The related cell value.
     */
    public function get_related_cell_where(
        DataStorageTable $related_table,
        string $related_return_column,
        string ...$where_condition,
    ): mixed;

    /**
     * Add a new row to the table.
     *
     * @param array $key_value_pairs An array of key-value pairs for the new row.
     * @return DataStorageTableInterface The updated table instance.
     */
    public function add_row(array $key_value_pairs): DataStorageTableInterface;

    /**
     * Set the value of a cell in a row by ID.
     *
     * @param int $id The ID of the row.
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @return DataStorageTableInterface The updated table instance.
     */
    public function set_cell(int $id, string $column, mixed $value): DataStorageTableInterface;

    /**
     * Set the value of a cell based on WHERE conditions.
     *
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @param mixed $where_condition The WHERE condition.
     * @return DataStorageTableInterface The updated table instance.
     */
    public function set_cell_where(string $column, mixed $value, $where_condition): DataStorageTableInterface;

    /**
     * Delete a row by its ID.
     *
     * @param int $id The ID of the row to delete.
     * @return DataStorageTableInterface The updated table instance.
     */
    public function delete_row(int $id): DataStorageTableInterface;

    /**
     * Delete rows based on WHERE conditions.
     *
     * @param mixed $where_condition The WHERE condition.
     * @return DataStorageTableInterface The updated table instance.
     */
    public function delete_row_where($where_condition): DataStorageTableInterface;
}

class DataStorageTable
{
    public function __construct(public string $name)
    {
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
