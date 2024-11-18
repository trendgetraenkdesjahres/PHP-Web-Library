<?php

namespace  PHP_Library\Database;

use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\SQLanguage\Statement\AbstractStatement;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\DataTable;
use PHP_Library\Settings\Settings;
use ReflectionClass;

/**
 * Database is a place for tables stored in Databases or Files
 */
abstract class Database
{
    /**
     * Holds the initialized storage instance implementation. It's can not be this abstract class Database, that will not work.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Initialize the database connection.
     *
     * @return bool Returns true if initialization is successful, false otherwise.
     */
    abstract protected static function initalize(): bool;

    abstract public static function last_insert_id(): int|false;

    /**
     * Get queried data.
     *
     * @param bool $clean_array if the result is an array, and has just one element, it will return this element (recursivly).
     * @return mixed The queried data.
     */
    abstract protected static function get_queried_data(bool $clean_array = false): mixed;

    /**
     * Internal implementation of the execution of a SQL Statement
     * @param AbstractStatement $sql_statement The SQL statement to execute.
     * @return bool success.
     */
    abstract protected static function execute_query(AbstractStatement $sql_statement): bool;

    /**
     * Database is a place for tables stored in Databases or Files.
     * It will be constructed by setting for `[Database]/database_name` or `[Database]/file_name` found in a `settings.ini`-file.
     * If using the FileDatabase, the location 'Database/file_name' needs to be writable by the php-server.
     */
    final public function __construct()
    {
        static::initalize();
    }

    /**
     * Execute SQL Statement.
     * Get the results with `get_queried_data()`
     * @param AbstractStatement $sql_statement The SQL statement to execute.
     * @return bool success.
     */
    final public static function query(AbstractStatement $sql_statement): bool
    {
        if (static::get_type() === __CLASS__) {
            DatabaseError::trigger("Database is not initiated.", fatal: true);
        }
        return self::get_instance()::execute_query($sql_statement);
    }

    /**
     * Get recent queried data.
     *
     * @param bool $clean_array if the result is an array, and has just one element, it will return this element (recursivly).
     * @return mixed The queried data.
     */
    public static function get_query_result(bool $clean_array = false): mixed
    {
        return self::get_instance()::get_queried_data($clean_array);
    }

    /**
     * Get a table instance by name.
     *
     * @param string $name The name of the table.
     * @return DataTable The table instance.
     */
    public static function get_table(string $name): DataTable
    {
        return self::get_instance()::get_table($name);
    }

    public static function get_last_insert_id(): int|false
    {
        return self::get_instance()::last_insert_id();
    }

    /**
     * Create a table instance based on the specified name and columns.
     *
     * @param string $name The name of the table.
     * @param Column ...$columns Variable-length list of TableColumn objects representing table columns.
     * @return bool Success.
     */
    public static function create_table(string $name, Column ...$columns): bool
    {
        return self::get_instance()::create_table($name, ...$columns);
    }

    public static function table_exists(string $name): bool
    {
        return self::get_instance()::table_exists($name);
    }

    public static function get_type(): string
    {
        return (new ReflectionClass(self::get_instance()))->getShortName();
    }

    public static function get_last_error(): DatabaseError|null
    {
        return isset(DatabaseError::$last_error) ? DatabaseError::$last_error : null;
    }

    /**
     * Factory method to get or initialize the appropriate storage instance.
     *
     * @return Database The initialized storage instance.
     * @throws DatabaseError If no suitable configuration for Filebased or DBbased setting is found.
     */
    protected static function get_instance(): static
    {
        if (self::$instance === null) {
            // Check settings to decide the storage type
            if (Settings::get('Database/database_name')) {
                self::$instance = new SQLDatabase();
            } elseif (Settings::get('Database/file_name')) {
                self::$instance = new FileDatabase();
            } else {
                DatabaseError::trigger("No setting for 'Database/database_name' or 'Database/file_name' found in settings-file.", fatal: true);
            }
        }
        return self::$instance;
    }

    protected static function clean_array(array $array): mixed
    {
        $count = count($array);
        switch ($count) {
            case 0:
                return [];
            case 1:
                $value = $array[array_key_first($array)];
                if (is_array($value)) {
                    return static::clean_array($value);
                }
                return $value;
            default:
                return $array;
        }
    }
}
