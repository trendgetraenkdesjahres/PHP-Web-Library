<?php

namespace PHP_Library\Database\FileDatabaseAggregate;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\FileDatabase;
use PHP_Library\Database\SQLanguage\Statement\Delete;
use PHP_Library\Database\SQLanguage\Statement\Insert;
use PHP_Library\Database\SQLanguage\Statement\Select;
use PHP_Library\Database\SQLanguage\Statement\Update;
use PHP_Library\Database\Table\FileTable;


/**
 * Trait providing functionality for executing SQL-like operations (INSERT, SELECT, UPDATE, DELETE) on a file-based database.
 * Depends on various classes from the `PHP_Library\Database` and `PHP_Library\Database\SQLanguage\Statement` namespaces.
 */
trait FileDatabaseAggregate
{
    /**
     * Executes a DELETE SQL statement on the file-based database.
     * @param Delete $sql_statement The DELETE statement.
     * @return int The number of rows deleted.
     */
    protected static function execute_delete(Delete $sql_statement): int
    {
        $row_ids = self::get_row_ids_from_where_clause($sql_statement);
        $deleted_rows = 0;
        foreach (FileDatabase::$data[$sql_statement->table] as $column_name => $column) {
            foreach ($row_ids as $i => $row_id) {
                unset(FileDatabase::$data[$sql_statement->table][$column_name][$row_id]);
                if ($column_name === array_key_last(FileDatabase::$data[$sql_statement->table])) {
                    $deleted_rows++;
                }
            }
        }
        return $deleted_rows;
    }

    /**
     * Executes an INSERT SQL statement on the file-based database.
     * @param Insert $sql_statement The INSERT statement.
     * @return int The row ID of the inserted record.
     */
    protected static function execute_insert(Insert $sql_statement): int
    {
        $table_name = $sql_statement->table;
        $columns_info = static::get_columns_info($table_name);
        $values = $sql_statement->values;

        $row_cells = [];
        if (! $sql_statement->columns_string || $sql_statement->columns_string == "*") {
            foreach ($columns_info as $column_name => $column_property) {
                if ($column_property['auto_increment']) {
                    $row_cells[$column_name] = null;
                } else {
                    $row_cells[$column_name] = array_shift($values);
                }
            }
        } else {
            foreach ($sql_statement->columns as $column_name) {
                if ($columns_info[$column_name]['auto_increment']) {
                    $row_cells[$column_name] = null;
                } else {
                    $row_cells[$column_name] = array_shift($values);
                }
            }
        }
        return static::insert_row($table_name, $row_cells);
    }

    /**
     * Executes a SELECT SQL statement on the file-based database.
     * @param Select $sql_statement The SELECT statement.
     * @return array The selected rows.
     */
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

    /**
     * Executes an UPDATE SQL statement on the file-based database.
     * @param Update $sql_statement The UPDATE statement.
     * @return int The number of rows updated.
     */
    protected static function execute_update(Update $sql_statement): int
    {
        $table_name = $sql_statement->table;
        $row_ids = static::get_row_ids_from_where_clause($sql_statement);
        $updated_rows = 0;
        foreach ($row_ids as $row_id) {
            foreach ($sql_statement->update_cells as $column_name => $value) {
                static::set_cell($table_name, $column_name, $row_id, $value);
            }
            $updated_rows++;
        }
        return $updated_rows;
    }

    /**
     * Retrieves row IDs based on a WHERE clause in a SQL statement.
     * @param Select|Delete|Update $sql_statement The SQL statement with the WHERE clause.
     * @return array The matching row IDs.
     */
    private static function get_row_ids_from_where_clause(Select|Delete|Update $sql_statement): array
    {
        $table_name = $sql_statement->table;
        if (! $sql_statement->get_where_clause()) {
            // assuming the first column is complete
            $first_column = array_key_first(FileDatabase::$data[$table_name]);
            return array_keys(FileDatabase::$data[$table_name][$first_column]);
        }
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

    /**
     * Operates on multiple arrays based on the given logical operator.
     * @param string $operator The logical operator (e.g., 'AND', 'OR').
     * @param array ...$arrays The arrays to operate on.
     * @return array The result of the operation.
     */
    private static function operate_row_array(string $operator, ...$arrays)
    {
        if (str_starts_with($operator, 'AND')) {
            return array_intersect(...$arrays);
        }
        return array_merge(...$arrays);
    }

    /**
     * Retrieves a specific row from the file-based database.
     * @param string $table The table name.
     * @param int $row_id The row ID.
     * @param string $select_column The column to select (default is "*").
     * @param string ...$select_columns Additional columns to select.
     * @return array The selected row.
     */
    private static function get_row(string $table, int $row_id, string $select_column = "*", string ...$select_columns): array
    {
        if ($select_column === "*") {
            $select_columns = array_keys(FileDatabase::$data[$table]);
            // hide the row_id column.
            $row_id_column = array_search(FileTable::$default_id_column_name, $select_columns);
            unset($select_columns[$row_id_column]);
        } else {
            $select_columns = array_merge([$select_column], $select_columns);
            $select_columns = array_map(function ($value) {
                return trim($value);
            }, $select_columns);
        }
        $row = [];
        foreach (FileDatabase::$data[$table] as $column => $entries) {
            if (in_array($column, $select_columns)) {
                $row[$column] = $entries[$row_id];
            }
        }
        return $row;
    }

    /**
     * Retrieves row IDs where a column equals a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to match.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column does not equal a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to avoid.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column is greater than a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to compare.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column is greater than or equal to a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to compare.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column is less than a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to compare.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column is less than or equal to a specific value.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to compare.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column value is between two values.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param int|float $lower The lower bound.
     * @param int|float $higher The upper bound.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column value matches a pattern.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The pattern to match.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column value is in a given set of values.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param array $values The set of values.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves row IDs where a column value is not in a given set of values.
     * @param string $table The table name.
     * @param string $column The column name.
     * @param array $values The set of values.
     * @return array The matching row IDs.
     */
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

    /**
     * Retrieves column information for a specific table.
     * @param string $table_name The table name.
     * @return array The column information.
     */
    private static function get_columns_info(string $table_name): array
    {
        $columns_info = FileDatabase::$data['%tables'][$table_name];
        unset($columns_info['%primary_key']);
        return $columns_info;
    }

    /**
     * Retrieves the primary key column name for a specific table.
     * @param string $table_name The table name.
     * @return string The primary key column name.
     */
    private static function get_primary_key(string $table_name): string
    {
        return FileDatabase::$data['%tables'][$table_name]['%primary_key'];
    }

    /**
     * Sets the value of a specific cell in a table.
     * @param string $table_name The table name.
     * @param string $column_name The column name.
     * @param int $row_id The row ID.
     * @param mixed $value The value to set.
     * @return bool Returns true on success, false on failure.
     */
    private static function set_cell(string $table_name, string $column_name, int $row_id, mixed $value = null): bool
    {
        $columns_info = static::get_columns_info($table_name);
        if (! key_exists($column_name, $columns_info)) {
            return false;
        }
        $primary_key = static::get_primary_key($table_name);
        if ($column_name == $primary_key) {
            if (false !== array_search($value, FileDatabase::$data[$table_name][$column_name], true)) {
                DatabaseError::trigger("$table_name.$column_name (primary key) must be unique.");
                return false;
            }
        }
        if ($columns_info[$column_name]['auto_increment']) {
            $last_ai_value_key = array_key_last(FileDatabase::$data[$table_name][$column_name]);
            $last_ai_value = is_null($last_ai_value_key) ? 0 : FileDatabase::$data[$table_name][$column_name][$last_ai_value_key];
            FileDatabase::$data[$table_name][$column_name][$row_id] = $last_ai_value + 1;
            return true;
        }
        if ($columns_info[$column_name]['timestamp']) {
            FileDatabase::$data[$table_name][$column_name][$row_id] = date('Y-m-d H:i:s', time());
            return true;
        }
        if (! static::is_value_in_column_allowed($table_name, $column_name, $value)) {
            DatabaseError::trigger("Value for '$column_name' needs to be type of '{$columns_info[$column_name]['type']}' in '$table_name'.");
            return false;
        }
        FileDatabase::$data[$table_name][$column_name][$row_id] = $value;
        return true;
    }

    /**
     * Sets the values for an entire row in a table.
     * @param string $table_name The table name.
     * @param int|null $row_id The row ID (optional).
     * @param array $row_cells The values to set in the row.
     * @return int The number of cells set.
     */
    private static function set_row(string $table_name, ?int $row_id = null, array $row_cells = []): int
    {
        $columns_info = static::get_columns_info($table_name);
        $set_cells = 0;
        foreach ($columns_info as $column_name => $column_property) {
            // if it's an auto increment column but a value is given.
            if ($column_property['auto_increment'] && isset($row_cells[$column_name]) && $row_cells[$column_name]) {
                DatabaseError::trigger("$table_name.$column_name is an auto increment column. can not accept value other than null.");
            }
            // if the value is not set / empty.
            if ((!isset($row_cells[$column_name]) || ! $row_cells[$column_name]) && !$column_property['nullable']) {
                // exception: auto increment will always be unset. set it here to null,
                if (! $column_property['auto_increment']) {
                    DatabaseError::trigger("$table_name.$column_name can not be empty/null");
                } else {
                    $row_cells[$column_name] = null;
                }
            } else {
                $value = $row_cells[$column_name];
            }
            unset($row_cells[$column_name]);
            $set_cells = $set_cells + (int) static::set_cell($table_name, $column_name, $row_id, $value);
        }
        if (!empty($row_cells)) {
            DatabaseError::trigger("Missing keys: " . implode(', ', array_keys($row_cells)));
        }
        return $set_cells;
    }

    /**
     * Inserts a new row into a specific table.
     * @param string $table_name The table name.
     * @param array $row_cells The values for the new row.
     * @return int The row ID of the inserted row.
     */
    private static function insert_row(string $table_name, array $row_cells): int
    {
        $columns_info = static::get_columns_info($table_name);
        $new_row_key = static::get_new_insert_row_key($table_name);
        foreach ($columns_info as $column_name => $column_property) {
            if ($column_property['auto_increment']) {
                static::set_cell($table_name, $column_name, $new_row_key, null);
                continue;
            }
            if (!isset($row_cells[$column_name]) && !$column_property['nullable']) {
                DatabaseError::trigger("$table_name.$column_name can not be empty/null");
                continue;
            }
            $value = $row_cells[$column_name];
            unset($row_cells[$column_name]);
            static::set_cell($table_name, $column_name, $new_row_key, $value);
        }
        return isset($new_row_key) ? $new_row_key + 1 : 1;
    }

    // 'key' in array start with 0, 'id' in a table row with 1
    private static function get_new_insert_row_key(string $table_name): int
    {
        $first_column = array_key_first(FileDatabase::$data[$table_name]);
        $highest_current_key = array_key_last(FileDatabase::$data[$table_name][$first_column]);
        if (is_int($highest_current_key)) {
            return $highest_current_key + 1;
        }
        return 0;
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
