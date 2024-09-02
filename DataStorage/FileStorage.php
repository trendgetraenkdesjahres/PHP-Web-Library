<?php

namespace  PHP_Library\DataStorage;

use PHP_Library\DataStorage\Table\Column;
use PHP_Library\DataStorage\Table\FileTable;
use PHP_Library\Error\Error;
use PHP_Library\Error\Warning;
use  PHP_Library\Settings\Settings;
use  PHP_Library\System\FileHandle;


/**
 * FileStorage is a class that handles data storage using files.
 */
class FileStorage extends DataStorage
{
    protected static mixed $data = null;
    private static FileHandle $file;
    public static mixed $query_result = [];

    /**
     * Initialize the file storage.
     *
     * @return bool Returns true if initialization is successful, false otherwise.
     */
    public static function initalize(): bool
    {
        Settings::register('datastorage/file_name');
        $path = Settings::get('datastorage/file_name');
        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = getcwd() . '/' . $path;
        }


        self::$file = new FileHandle($path);
        try {
            self::$file
                ->create_file(force: false)
                ->open_file()
                ->close_file();
            self::$data = self::$file->get_memory();
            return true;
        } catch (\Throwable $e) {
            Warning::trigger($e->getMessage());
            return false;
        }
    }

    /**
     * Get the list of tables.
     *
     * @return array The list of table names.
     */
    public static function get_tables(): array
    {
        self::get_file()
            ->open_file()
            ->close_file();
        self::$data = self::$file->get_memory();
        return array_keys(self::$data);
    }

    /**
     * Check if a table exists.
     *
     * @param string $table The table name.
     * @return bool Returns true if the element exists, false otherwise.
     */
    public static function table_exists(string $table, ?string $throwable = null): bool
    {
        if (!self::$data) {
            if ($throwable) {
                throw new $throwable("DataStorage is empty.");
            }
            return false;
        }
        if (!key_exists($table, self::$data)) {
            if ($throwable) {
                throw new $throwable("Table '$table' does not exist");
            }
            return false;
        }
        return true;
    }

    /**
     * Check if a table element exists.
     *
     * @param string $table The table name.
     * @param string|null $column The column name.
     * @param string|null $row The row name.
     * @param string|null $throwable The throwable to use for exceptions.
     * @return bool Returns true if the element exists, false otherwise.
     */
    public static function table_element_exists(string $table, ?string $column = null, ?string $row = null, ?string $throwable = null): bool
    {
        if (!self::table_exists($table, $throwable)) {
            if ($throwable) {
                throw new $throwable("Table '$table' does not exist");
            }
            return false;
        }
        if ($column && !key_exists($column, self::$data[$table])) {
            if ($throwable) {
                throw new $throwable("Column '$table.$column' does not exist");
            }
            return false;
        }
        if ($row && !key_exists($row, self::$data[$table][$column])) {
            if ($throwable) {
                throw new $throwable("Row '$row' in '$table.$column' does not exist");
            }
            return false;
        }
        return true;
    }

    /**
     * Check if a value is allowed in a column.
     *
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to check.
     * @param string|null $throwable The throwable to use for exceptions.
     * @return bool Returns true if the value is allowed, false otherwise.
     */
    public static function is_value_in_column_allowed(string $table, string $column, mixed $value, ?string $throwable = null): bool
    {
        $column_name = $column;
        $column_property = self::$data['%tables'][$table][$column];
        if (
            !$column_property['timestamp']
            && gettype($value) !==  $column_property['type']
        ) {
            if ($value === null && !$column_property['nullable']) {
                throw new $throwable("Value for '$column_name' needs to be type of '{$column_property['type']}' in '$table'.");
                return false;
            }
        }
        return true;
    }

    /**
     * Create a table with the given name and columns.
     *
     * @param string $table The name of the table.
     * @param TableColumn ...$columns The columns to create.
     * @return bool Returns true if the table is created, false if it already exists.
     */
    public static function create_table(string $table, Column ...$columns): bool
    {
        if (self::$data) {
            if (key_exists($table, self::$data)) {
                Warning::trigger("'$table' already exists. Not created.");
                return false;
            }

            //meta table-info table
            if (!key_exists('%tables', self::$data)) {
                new FileTable('%tables', true);
            }
        } else {
            self::$data = [];
        }

        self::$data[$table] = [];
        foreach ($columns as $column) {
            self::$data[$table][$column->name] = [];
            self::$data['%tables'][$table][$column->name] =
                [
                    'type' => $column->type,
                    'nullable' => $column->nullable,
                    'timestamp' => $column->timestamp,
                ];
        }
        self::get_file()
            ->open_file('r+', false)
            ->write_file(self::$data)
            ->close_file();
        return true;
    }

    public static function get_data(): array
    {
        return self::$data;
    }

    /**
     * Create a row in a table with key-value pairs.
     *
     * @param string $table The table name.
     * @param array $key_value_pairs The key-value pairs for the row.
     */
    public static function create_table_row(string $table, array $key_value_pairs)
    {
        $cells_that_need_to_be_written = count(self::$data['%tables'][$table]);
        foreach (self::$data['%tables'][$table] as $column_name => $column_property) {
            if (
                !isset($key_value_pairs[$column_name])
                && (!$column_property['nullable']
                    && !$column_property['timestamp'])
            ) {
                throw new \Error("Value for '$column_name' can't be empty in the Table '$table'.");
            }
            self::is_value_in_column_allowed($table, $column_name, $key_value_pairs[$column_name], 'Error');

            if ($column_property['timestamp']) {
                $cells_that_need_to_be_written--;
                array_push(self::$data[$table][$column_name], date('Y-m-d H:i:s', time()));
                continue;
            }
            $cells_that_need_to_be_written--;
            array_push(self::$data[$table][$column_name], $key_value_pairs[$column_name]);
        }
        Error::if(
            $cells_that_need_to_be_written !== 0,
            "Missing fields for '$table'"
        );
        self::get_file()
            ->open_file('r+', false)
            ->write_file(self::$data)
            ->close_file();
    }

    /**
     * Set a cell's value in a table.
     *
     * @param string $table The table name.
     * @param int $id The row ID.
     * @param string $column The column name.
     * @param mixed $value The new value.
     */
    public static function set_cell(string $table, int $id, string $column, mixed $value)
    {
        if (self::table_element_exists($table, $column, $id, 'Warning')) {
            self::$data[$table][$column][$id] = $value;
        }
    }

    /**
     * Execute a query on a table.
     *
     * @param string $table The table name.
     * @param string $query The query string.
     * @param string|null $column The column name.
     * @param string $command The query command ('get' or 'set').
     * @param mixed $value The value for 'set' command.
     */
    public static function query(string $table, string $where_condition, ?string $column = null, string $command = 'get', mixed $value = null, ?\throwable $error_level = null)
    {

        if (strpos($where_condition, " AND ")) {
            throw new \Error("Multiple WHERE clause are not implemented yet");
        }
        if ($command == 'get') {
            self::$file
                ->open_file()
                ->close_file();
            self::$data = self::$file->get_memory();
        } else {
            self::$file->open_file('r+');
            self::$data = self::$file->get_memory();
        }
        if (!self::table_element_exists($table, $column, throwable: $error_level)) {
            return [];
        }
        if ($column) {
            $columns = [$column];
        } else {
            $columns = array_keys(self::$data[$table]);
        }
        $sql_operators = [
            '=',
            '>',
            '>=',
            '<',
            '<=',
            '<>',
            'BETWEEN',
            'LIKE',
            'IN',
            'NOT IN',
        ];
        $condition_column = $condition_operatant = $condition_value = "??";
        try {
            foreach ($sql_operators as $operator) {
                if (is_int(strpos(
                    haystack: $where_condition,
                    needle: $operator
                ))) {
                    $array = explode($operator, $where_condition);
                    $condition_operatant = $operator;
                    $condition_column = trim($array[0]);
                    if ($condition_column != 'id') {
                        self::table_element_exists($table, $condition_column, throwable: 'Warning');
                    }
                    $condition_value = trim(trim($array[1]), "'\"");
                    break;
                }
            }
        } catch (\Throwable $e) {
            PHP_Library\Warning::trigger("WHERE clause is broken: '$where_condition'");
        }


        switch ($command) {
            case 'get':
                switch ($condition_operatant) {
                    case '=':
                        if ($condition_column == 'id') {
                            $ids = [$condition_value];
                        } else {
                            $ids = array_keys(
                                array: self::$data[$table][$condition_column],
                                filter_value: $condition_value,
                            );
                        }
                        foreach ($ids as $i => $id) {
                            foreach ($columns as $column) {
                                if (!isset(self::$data[$table][$column][$id])) {
                                    throw new \Error("id=$id in '$table' is not set.");
                                }
                                self::$query_result[$i][$column] = self::$data[$table][$column][$id];
                            }
                            if (count(self::$query_result[$i]) > 1) {
                                self::$query_result[$i]['id'] = $id;
                            }
                        }
                        break;
                    default:
                        PHP_Library\Warning::trigger("'$command' with operant '$condition_operatant': not implmented.");
                        break;
                }
                break;

            case 'set':
                switch ($condition_operatant) {
                    case '=':
                        $id = $condition_value;
                        if (!self::table_element_exists($table, $column, $id, 'Error')) {
                            break;
                        }
                        if (self::is_value_in_column_allowed($table, $column, $value, 'Error')) {
                            self::$data[$table][$column][$id] = $value;
                        }
                        self::$file->write_file(self::$data);
                        break;


                    default:
                        PHP_Library\Warning::trigger("'$command' with operant '$condition_operatant': not implmented.");
                        break;
                }
                break;



            default:
                # code...
                break;
        }
        self::$file
            ->close_file();
    }

    /**
     * Get the queried data.
     *
     * @return mixed The queried data.
     */
    public static function get_queried_data(): mixed
    {
        return self::$query_result;
    }


    /**
     * Method count_queried_data
     *
     * @return int
     */
    public static function count_queried_data(): int
    {
        // TODO auch in db variante implementieren & ins interface
        return count(self::$query_result);
    }

    /**
     * Get a table instance by name.
     *
     * @param string $table_name The name of the table.
     * @return FileTable The table instance.
     */
    public static function get_table(string $table_name): FileTable
    {
        return new FileTable($table_name, false);
    }

    private static function get_file(): FileHandle
    {
        if (!isset(self::$file)) {
            self::initalize();
        }
        return self::$file;
    }
}
