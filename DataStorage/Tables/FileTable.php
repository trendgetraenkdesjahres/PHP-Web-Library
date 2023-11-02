<?php

namespace DataStorage;

/**
 * FileTable is a concrete implementation of DataStorageTable for file-based tables.
 */
class FileTable extends DataStorageTable implements DataStorageTableInterface
{
    /**
     * Get a row from the file-based table by its ID.
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    public function get_row(int $id): array
    {
        FileStorage::query(
            table: $this->name,
            where_condition: "id = $id",
            command: 'get'
        );
        return FileStorage::get_queried_data();
    }

    /**
     * Get a cell from the file-based table based on WHERE conditions.
     *
     * @param string $return_column The column to retrieve.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The cell value.
     */
    public function get_cell_where(string $return_column, string ...$where_condition): mixed
    {
        $where_conditions = implode(" AND ", $where_condition);
        FileStorage::query(
            table: $this->name,
            where_condition: $where_conditions,
            column: $return_column,
            command: 'get'
        );
        return FileStorage::get_queried_data()[$return_column];
    }

    /**
     * Get any rows from the file-based table based on WHERE conditions.
     *
     * @param string ...$where_condition The WHERE conditions.
     * @return array An array of rows.
     */
    public function get_any_row_where(?string ...$where_condition): array
    {
        $where_conditions = implode(" AND ", $where_condition);
        FileStorage::query(
            table: $this->name,
            where_condition: $where_conditions,
            command: 'get'
        );
        return FileStorage::get_queried_data();
    }

    /**
     * Add a new row to the file-based table.
     *
     * @param array $key_value_pairs An array of key-value pairs for the new row.
     * @return DataStorageTable The updated table instance.
     */
    public function add_row(array $key_value_pairs): DataStorageTable
    {
        FileStorage::create_table_row(
            table: $this->name,
            key_value_pairs: $key_value_pairs
        );
        return $this;
    }

    /**
     * Set the value of a cell in a row by ID.
     *
     * @param int $id The ID of the row.
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @return DataStorageTable The updated table instance.
     */
    public function set_cell(int $id, string $column, mixed $value): DataStorageTable
    {
        FileStorage::query(
            table: $this->name,
            where_condition: "id=$id",
            column: $column,
            command: 'set',
            value: $value
        );
        return $this;
    }

    /**
     * Set the value of a cell based on WHERE conditions.
     *
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @param mixed $where_condition The WHERE condition.
     * @return DataStorageTable The updated table instance.
     */
    public function set_cell_where(string $column, mixed $value, $where_condition): DataStorageTable
    {
        FileStorage::query(
            table: $this->name,
            where_condition: $where_condition,
            column: $column,
            command: 'set',
            value: $value
        );
        return $this;
    }

    /**
     * Delete a row by its ID.
     *
     * @param int $id The ID of the row to delete.
     * @return DataStorageTable The updated table instance.
     */
    public function delete_row(int $id): DataStorageTable
    {
        return $this;
    }

    public function get_related_cell(
        int $id,
        DataStorageTable $related_table,
        string $related_return_column,
    ): mixed {
    }

    /**
     * Delete rows based on WHERE conditions.
     *
     * @param mixed $where_condition The WHERE condition.
     * @return DataStorageTable The updated table instance.
     */
    public function delete_row_where($where_condition): DataStorageTable
    {
        return $this;
    }

    public function get_related_cell_where(
        DataStorageTable $related_table,
        string $related_return_column,
        string ...$where_condition,
    ): mixed {
    }
}
