<?php

namespace DataStorage;

/**
 * FileTable is a concrete implementation of DataStorageTable for file-based tables.
 */
class FileTable extends DataStorageTable implements DataStorageTableInterface
{
    public function get_name(): string
    {
        return $this->name;
    }

    public function get_row(int $id): array
    {
        FileStorage::query(
            table: $this->name,
            where_condition: "id = $id",
            command: 'get'
        );
        return FileStorage::get_queried_data();
    }

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

    public function add_row(array $key_value_pairs): DataStorageTableInterface
    {
        FileStorage::create_table_row(
            table: $this->name,
            key_value_pairs: $key_value_pairs
        );
        return $this;
    }

    public function set_cell(int $id, string $column, mixed $value): DataStorageTableInterface
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

    public function set_cell_where(string $column, mixed $value, $where_condition): DataStorageTableInterface
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

    public function delete_row(int $id): DataStorageTableInterface
    {
        return $this;
    }

    public function get_related_cell(
        int $id,
        DataStorageTable $related_table,
        string $related_return_column,
    ): mixed {
    }

    public function delete_row_where($where_condition): DataStorageTableInterface
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
