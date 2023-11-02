<?php

namespace DataStorage;

use PDO;

class DatabaseTable extends DataStorageTable implements DataStorageTableInterface
{
    /**
     * Get a row from the database table by its ID.
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    public function get_row(int $id): array
    {
        DatabaseStorage::query("SELECT * FROM {$this->name} WHERE id = $id;");
        return DatabaseStorage::get_queried_data(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Get a cell from the database table based on WHERE conditions.
     *
     * @param string $return_column The column to retrieve.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The cell value.
     */
    public function get_cell_where(string $return_column, string ...$where_condition): mixed
    {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT $return_column FROM {$this->name} WHERE $where_conditions;");
        return DatabaseStorage::get_queried_data()[0][$return_column];
    }

    /**
     * Get any rows from the database table based on WHERE conditions.
     *
     * @param string ...$where_condition The WHERE conditions.
     * @return array An array of rows.
     */
    public function get_any_row_where(?string ...$where_condition): array
    {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT * FROM {$this->name} WHERE $where_conditions;");
        return DatabaseStorage::get_queried_data(PDO::FETCH_ASSOC);
    }

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
    ): mixed {
        DatabaseStorage::query("SELECT $related_return_column FROM {$related_table->name} WHERE id = $id;");
        return DatabaseStorage::get_queried_data();
    }

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
    ): mixed {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT $related_return_column FROM {$related_table->name} WHERE id = $where_conditions;");
        return DatabaseStorage::get_queried_data();
    }

    /**
     * Add a new row to the database table.
     *
     * @param array $key_value_pairs An array of key-value pairs for the new row.
     * @return DataStorageTable The updated table instance.
     */
    public function add_row(array $key_value_pairs): DataStorageTable
    {
        $keys = implode(", ", array_keys($key_value_pairs));
        $values = "'" . implode("', '", array_values($key_value_pairs)) . "'";
        DatabaseStorage::query("INSERT INTO {$this->name} ($keys) VALUES ($values);");
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
        DatabaseStorage::query("UPDATE {$this->name} SET $column = '$value' WHERE id = $id;");
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
        DatabaseStorage::query("UPDATE {$this->name} SET $column = '$value' WHERE $where_condition;");
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
        DatabaseStorage::query("DELETE FROM {$this->name} WHERE id = $id;");
        return $this;
    }

    /**
     * Delete rows based on WHERE conditions.
     *
     * @param mixed $where_condition The WHERE condition.
     * @return DataStorageTable The updated table instance.
     */
    public function delete_row_where($where_condition): DataStorageTable
    {
        DatabaseStorage::query("DELETE FROM {$this->name} WHERE $where_condition;");
        return $this;
    }
}
