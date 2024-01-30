<?php

namespace Library\DataStorage\Table\Traits;

use Library\DataStorage\DatabaseStorage;
use Library\DataStorage\Table\Table;
use Library\DataStorage\Table\TableInterface;

trait Database
{
    public function get_row(int $id): array
    {
        DatabaseStorage::query("SELECT * FROM {$this->name} WHERE id = $id;");
        return DatabaseStorage::get_queried_data(\PDO::FETCH_ASSOC)[0];
    }

    public function get_cell_where(string $return_column, string ...$where_condition): mixed
    {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT $return_column FROM {$this->name} WHERE $where_conditions;");
        return DatabaseStorage::get_queried_data()[0][$return_column];
    }

    public function get_any_row_where(?string ...$where_condition): array
    {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT * FROM {$this->name} WHERE $where_conditions;");
        return DatabaseStorage::get_queried_data(\PDO::FETCH_ASSOC);
    }

    public function get_related_cell(
        int $id,
        Table $related_table,
        string $related_return_column,
    ): mixed {
        DatabaseStorage::query("SELECT $related_return_column FROM {$related_table->name} WHERE id = $id;");
        return DatabaseStorage::get_queried_data();
    }

    public function get_related_cell_where(
        Table $related_table,
        string $related_return_column,
        string ...$where_condition,
    ): mixed {
        $where_conditions = implode(" AND ", $where_condition);
        DatabaseStorage::query("SELECT $related_return_column FROM {$related_table->name} WHERE id = $where_conditions;");
        return DatabaseStorage::get_queried_data();
    }

    public function add_row(array $key_value_pairs): TableInterface
    {
        $keys = implode(", ", array_keys($key_value_pairs));
        $values = "'" . implode("', '", array_values($key_value_pairs)) . "'";
        DatabaseStorage::query("INSERT INTO {$this->name} ($keys) VALUES ($values);");
        return $this;
    }

    public function set_cell(int $id, string $column, mixed $value): TableInterface
    {
        DatabaseStorage::query("UPDATE {$this->name} SET $column = '$value' WHERE id = $id;");
        return $this;
    }

    public function set_cell_where(string $column, mixed $value, $where_condition): TableInterface
    {
        DatabaseStorage::query("UPDATE {$this->name} SET $column = '$value' WHERE $where_condition;");
        return $this;
    }

    public function delete_row(int $id): TableInterface
    {
        DatabaseStorage::query("DELETE FROM {$this->name} WHERE id = $id;");
        return $this;
    }

    public function delete_row_where($where_condition): TableInterface
    {
        DatabaseStorage::query("DELETE FROM {$this->name} WHERE $where_condition;");
        return $this;
    }
}
