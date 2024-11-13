<?php

namespace PHP_Library\Database\FileDatabaseAggregate;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\FileDatabase;
use PHP_Library\Database\SQLanguage\Statement\Delete;
use PHP_Library\Database\SQLanguage\Statement\Insert;
use PHP_Library\Database\SQLanguage\Statement\Select;
use PHP_Library\Database\SQLanguage\Statement\Update;

trait FileDatabaseAggregate
{
    protected static function execute_delete(Delete $sql_statement): int
    {
        $row_ids = self::get_row_ids_from_where_clause($sql_statement);
        $deleted_rows = 0;
        foreach (FileDatabase::$data[$sql_statement->table] as $column) {
            foreach ($row_ids as $i => $row_id) {
                unset($column[$row_id]);
                if ($i === array_key_last($row_ids)) {
                    $deleted_rows++;
                }
            }
        }
        return $deleted_rows;
    }

    protected static function execute_insert(Insert $sql_statement): int
    {
        $table_name = $sql_statement->table;
        $columns_info = static::get_columns_info($table_name);
        $values = $sql_statement->values;

        $row_cells = [];
        if (! $sql_statement->columns_string || $sql_statement->columns_string == "*") {
            foreach ($columns_info as $column_name => $column_property) {
                $row_cells[$column_name] = array_shift($values);
            }
        } else {
            foreach ($sql_statement->columns as $column_name) {
                $row_cells[$column_name] = array_shift($values);
            }
        }
        $sql_statement->values_clause;
        return static::insert_row($table_name, $row_cells);
    }

    protected static function execute_select(Select $sql_statement): array
    {
        $table_name = $sql_statement->table;
        $row_ids = self::get_row_ids_from_where_clause($sql_statement);
        $rows = [];
        if ($sql_statement->columns_string === "*") {
            foreach ($row_ids as $row_id) {
                $rows[] = static::get_row($table_name, $row_id);
            }
        } else if ($select_columns = explode(',', $sql_statement->columns_string)) {
            $first_column = array_shift($select_columns);
            foreach ($row_ids as $row_id) {
                $rows[] = static::get_row($table_name, $row_id, $first_column, ...$select_columns);
            }
        } else {
            foreach ($row_ids as $row_id) {
                $rows[] = static::get_row($table_name, $row_id, $sql_statement->columns_string);
            }
        }
        return $rows;
    }

    protected static function execute_update(Update $sql_statement): int
    {
        $table_name = $sql_statement->table;
        $row_ids = static::get_row_ids_from_where_clause($sql_statement);
        $updated_rows = 0;
        foreach ($row_ids as $row_id) {
            static::set_row($table_name, $row_id, $sql_statement->update_cells);
        }
        return $updated_rows;
    }

    /**
     * Get the row-IDs where the 'WHERE-Clause' matches
     *
     * @param Select $sql_statement Statement with WHERE clause
     * @return array the IDs
     */
    private static function get_row_ids_from_where_clause(Select|Delete|Update $sql_statement): array
    {
        $table_name = $sql_statement->table;
        $row_ids = [];
        foreach ($sql_statement->get_where_objs() as $and_or_or => $where_clause) {
            $l_operant = $where_clause->lhs;
            $r_operant = $where_clause->rhs;
            $r_operant2 = isset($where_clause->rhs2) ? $where_clause->rhs2 : null;
            switch ($where_clause->operator) {
                case '=':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_equals($table_name, $l_operant, $r_operant));
                    break;
                case '>':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_greater_than($table_name, $l_operant, $r_operant));
                    break;
                case '>=':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_greater_or_equal($table_name, $l_operant, $r_operant));
                    break;
                case '<':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_smaller($table_name, $l_operant, $r_operant));
                    break;
                case '<=':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_smaller_or_equal($table_name, $l_operant, $r_operant));
                    break;
                case '<>':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_not_equals($table_name, $l_operant, $r_operant));
                    break;
                case 'BETWEEN':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_between($table_name, $l_operant, $r_operant, $r_operant2));
                    break;
                case 'LIKE':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_like($table_name, $l_operant, $r_operant));
                    break;
                case 'IN':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_in($table_name, $l_operant, $r_operant));
                    break;
                case 'NOT IN':
                    $row_ids = static::operate_row_array($and_or_or, $row_ids, static::get_ids_where_not_in($table_name, $l_operant, $r_operant));
                    break;
                default:
                    DatabaseError::trigger("Method for '{$where_clause->operator}'-operator not implemented.");
            }
        }
        return $row_ids;
    }

    private static function operate_row_array(string $operator, ...$arrays)
    {
        if (str_starts_with($operator, 'AND')) {
            return array_intersect(...$arrays);
        }
        return array_merge(...$arrays);
    }

    private static function get_row(string $table, int $row_id, string $select_column = "*", string ...$select_columns): array
    {
        if ($select_column === "*") {
            $select_columns = array_keys(FileDatabase::$data[$table]);
        } else {
            $select_columns = [$select_column] + $select_columns;
        }
        $row = [];
        foreach (FileDatabase::$data[$table] as $column => $entries) {
            if (in_array($column, $select_columns)) {
                $row[$column] = $entries[$row_id];
            }
        }
        return $row;
    }

    private static function get_ids_where_equals(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell === $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_not_equals(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell !== $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_greater_than(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell > $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_greater_or_equal(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell >= $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_less_than(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell < $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_less_than_or_equal(string $table, string $column, mixed $value): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($cell <= $value) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_between(string $table, string $column, int|float $lower, int|float $higher): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if ($lower < $cell && $cell < $higher) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_like(string $table, string $column, mixed $value): array
    {
        $ids = [];
        // '%'-Wildcard
        $value = preg_replace('/(?<!\\)%/', '.*', $value);
        // '_'-Wildcard
        $value = preg_replace('/(?<!\\)_/', '.', $value);
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if (preg_match($value, $cell)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_in(string $table, string $column, array $values): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if (array_search($cell, $values)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_ids_where_not_in(string $table, string $column, array $values): array
    {
        $ids = [];
        foreach (FileDatabase::$data[$table][$column] as $id => $cell) {
            if (! array_search($cell, $values)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private static function get_columns_info(string $table_name): array
    {
        return FileDatabase::$data['%tables'][$table_name];
    }

    private static function set_cell(string $table_name, string $column_name, int $row_id, mixed $value = null): bool
    {
        $columns_info = static::get_columns_info($table_name);
        if (! key_exists($column_name, $columns_info)) {
            return false;
        }
        if ($columns_info[$column_name]['timestamp']) {
            FileDatabase::$data[$table_name][$column_name][$row_id] = date('Y-m-d H:i:s', time());
            return true;
        }
        if (! static::is_value_in_column_allowed($table_name, $column_name, $value)) {
            DatabaseError::trigger("Value for '$column_name' needs to be type of '{$columns_info[$column_name]['type']}' in '$table_name'.");
        }
        FileDatabase::$data[$table_name][$column_name][$row_id] = $value;
        return true;
    }

    private static function set_row(string $table_name, ?int $row_id = null, array $row_cells = []): int
    {
        $columns_info = static::get_columns_info($table_name);
        $set_cells = 0;
        foreach ($columns_info as $column_name => $column_property) {
            if (!isset($row_cells[$column_name]) && !$column_property['nullable']) {
                DatabaseError::trigger("$table_name.$column_name can not be empty/null");
            }
            $value = $row_cells[$column_name];
            unset($row_cells[$column_name]);
            static::set_cell($table_name, $column_name, $row_id, $value);
            $set_cells++;
        }
        if (!empty($row_cells)) {
            DatabaseError::trigger("Missing keys: " . implode(', ', array_keys($row_cells)));
        }
        return $set_cells;
    }

    private static function insert_row(string $table_name, array $row_cells): int
    {
        $columns_info = static::get_columns_info($table_name);
        $set_cells = 0;
        foreach ($columns_info as $column_name => $column_property) {
            if (!isset($row_cells[$column_name]) && !$column_property['nullable']) {
                DatabaseError::trigger("$table_name.$column_name can not be empty/null");
            }
            $new_id = isset($new_id) ? $new_id : count(FileDatabase::$data[$table_name][$column_name]);
            $value = $row_cells[$column_name];
            unset($row_cells[$column_name]);
            static::set_cell($table_name, $column_name, $new_id, $value);
            $set_cells++;
        }
        return $set_cells;
    }

    private static function count_nullable_columns(string $table_name): int
    {
        $count_columns = 0;
        $columns_info = static::get_columns_info($table_name);
        foreach ($columns_info as $column_property) {
            if ($column_property['nullable']) {
                $count_columns--;
            }
        }
        return $count_columns + count($columns_info);
    }

    /**
     * Check if a value is allowed in a column.
     *
     * @param string $table The table name.
     * @param string $column_name The column name.
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is allowed, false otherwise.
     */
    private static function is_value_in_column_allowed(string $table, string $column_name, mixed $value): bool
    {
        $columns_infos = static::get_columns_info($table);
        if ($columns_infos[$column_name]['timestamp']) {
            return true;
        }
        if ($columns_infos[$column_name]['nullable'] && empty($value)) {
            return true;
        }
        if ($columns_infos[$column_name]['type'] == gettype($value) && !is_null($value)) {
            return true;
        }
        return false;
    }
}
