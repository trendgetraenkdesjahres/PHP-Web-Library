<?php

namespace  PHP_Library\Database;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\FileDatabaseAggregate\FileDatabaseAggregate;
use PHP_Library\Database\SQLanguage\Statement\AbstractStatement;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\Column\PrimaryAutoIncrementKey;
use PHP_Library\Database\Table\FileTable;
use PHP_Library\Error\Warning;
use  PHP_Library\Settings\Settings;
use  PHP_Library\System\FileHandle;
use ReflectionClass;

/**
 * FileStorage is a class that handles data storage using files.
 */
class FileDatabase extends Database
{
    use FileDatabaseAggregate;

    private static FileHandle $file;

    protected static int $last_insert_id;

    public static mixed $data = null;
    public static mixed $query_result = [];

    public function __destruct()
    {
        self::$file->close_file();
    }

    /**
     * Initialize the file storage.
     *
     * @return bool Returns true if initialization is successful, false otherwise.
     */
    public static function initalize(): bool
    {
        Settings::register('Database/file_name');
        $path = Settings::get('Database/file_name');
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
        } catch (\Throwable $t) {
            DatabaseError::trigger("Could not initalize File-Database connection: " . $t->getMessage());
        }
        register_shutdown_function(function () {
            self::$file->close_file();
        });
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

    public static function last_insert_id(): int|false
    {
        return isset(static::$last_insert_id) ? static::$last_insert_id : false;
    }
    /**
     * Create a table with the given name and columns.
     *
     * @param string $table The name of the table.
     * @param Column ...$columns The columns to create.
     * @return bool Returns true if the table is created, false if it already exists.
     */
    public static function create_table(string $table, Column ...$columns): bool
    {
        if (self::$data) {
            if (key_exists($table, self::$data)) {
                Warning::trigger("'$table' already exists. Not created.");
                return false;
            }
        } else {
            self::$data = [];
        }
        // check primary Key: there has be exactly one and in first place
        $primary_key_columns = [];
        foreach ($columns as $i => $column) {
            if ($column::$is_primary_key) {
                $primary_key_columns[] = $i;
            }
        }
        if (empty($primary_key_columns)) {
            $primary_key = new PrimaryAutoIncrementKey(FileTable::$default_id_column_name);
            array_unshift($columns, $primary_key);
        } else if (count($primary_key_columns) === 1) {
            $primary_key = $columns[$primary_key_columns[0]];
        } else {
            throw new DatabaseError("A table can have only ONE primary key.");
        }

        // build table
        self::$data[$table] = [];
        self::$data['%tables'][$table]['%primary_key'] = $primary_key->name;
        foreach ($columns as $column) {
            self::$data[$table][$column->name] = [];
            self::$data['%tables'][$table][$column->name] =
                [
                    'type' => $column->type,
                    'nullable' => $column->nullable,
                    'timestamp' => $column->timestamp,
                    'auto_increment' => isset($column::$auto_increment) ? $column::$auto_increment : false
                ];
        }
        self::dump_data_in_file();
        return true;
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
                throw new $throwable("Database is empty.");
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
     * Get the queried data.
     *
     * @return mixed The queried data.
     */
    protected static function get_queried_data(bool $clean_array = false): mixed
    {
        if ($clean_array) {
            return static::clean_array(self::$query_result);
        }
        return self::$query_result;
    }

    protected static function execute_query(AbstractStatement $sql_statement): bool
    {
        $command = strtoupper((new ReflectionClass($sql_statement))->getShortName());
        switch ($command) {
            case 'DELETE':
                static::$data = static::$file->open_file('r+')
                    ->get_memory();
                static::$query_result = static::execute_delete($sql_statement);
                static::$file->write_file(static::$data)
                    ->close_file();
                break;

            case 'INSERT':
                static::$data = static::$file->open_file('r+')
                    ->get_memory();
                $last_insert_id = static::execute_insert($sql_statement);
                if (! $last_insert_id) {
                    static::$query_result = false;
                } else {
                    static::$query_result = true;
                    static::$last_insert_id = $last_insert_id;
                }
                static::$file->write_file(static::$data)
                    ->close_file();
                break;

            case 'SELECT':
                static::$data = static::$file->open_file('r')
                    ->get_memory();
                static::$query_result = static::execute_select($sql_statement);
                static::$file->close_file();
                break;

            case 'UPDATE':
                static::$data = static::$file->open_file('r+')
                    ->get_memory();
                static::$query_result = static::execute_update($sql_statement);
                static::$file->write_file(static::$data)
                    ->close_file();
                break;

            default:
                DatabaseError::trigger("Executing '$command'-queries is not implemented.");
        }
        return (bool) static::$query_result;
    }

    private static function get_file(): FileHandle
    {
        if (!isset(self::$file)) {
            self::initalize();
        }
        return self::$file;
    }

    private static function dump_data_in_file(): void
    {
        self::get_file()
            ->open_file('r+', false)
            ->write_file(self::$data)
            ->close_file();
    }
}
