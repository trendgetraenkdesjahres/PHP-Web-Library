<?php

namespace  PHP_Library\Database;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\FileDatabaseAggregate\FileDatabaseAggregate;
use PHP_Library\Database\SQLanguage\Statement\AbstractStatement;
use PHP_Library\Database\Table\Column;
use PHP_Library\Database\Table\FileTable;
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
                static::$query_result = static::execute_insert($sql_statement);
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
