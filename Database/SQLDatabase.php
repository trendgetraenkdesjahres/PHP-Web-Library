<?php

namespace  PHP_Library\Database;

use  PHP_Library\Settings\Settings;
use PHP_Library\Database\SQLanguage\Statement\AbstractStatement;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\SQLTable;
use PHP_Library\Database\Error\DatabaseError;

/**
 * Handles database operations using PDO for SQL-based databases.
 * Provides methods for initializing the connection, querying, creating tables, and checking table existence.
 * Dependent on `Settings` for configuration and `DatabaseError` for error handling.
 */
class SQLDatabase extends Database
{
    /**
     * PDO instance for database connection.
     * @var PDO
     */
    private static \PDO $pdo;

    /**
     * PDOStatement instance to store query results.
     * @var PDOStatement
     */
    private static \PDOStatement $result;

    /**
     * Initialize the database connection using provided settings.
     * @return bool Returns true if initialization is successful, false otherwise.
     */
    protected static function initalize(): bool
    {
        Settings::register('Database/database_name');
        Settings::register('Database/database_username');
        Settings::register('Database/database_password');
        try {
            $databasename = Settings::get('Database/database_name', true);
            $username = Settings::get('Database/database_username', true);
            $password = Settings::get('Database/database_password', true);
        } catch (\Throwable $t) {
            throw new DatabaseError($t);
        }

        $host = ($host = Settings::get('Database/database_host')) ? $host : 'localhost';
        $port = ($port = Settings::get('Database/database_port')) ? $port : '3306';
        $charset = ($charset = Settings::get('Database/database_charset')) ? $charset : 'utf8mb4';
        $driver = ($driver = Settings::get('Database/database_driver')) ? $driver : 'mysql';

        $dns = "$driver:host=$host";
        $dns .= $databasename ? ";dbname=$databasename" : '';
        $dns .= ";port=$port";
        $dns .= ";charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new \PDO(
                $dns,
                $username,
                $password,
                $options
            );
        } catch (\Throwable $t) {
            DatabaseError::trigger("Could not initalize SQL-Database connection: " . $t->getMessage());
        }
        self::$pdo = $pdo;
        return true;
    }

    /**
     * Get the last inserted ID from the database.
     * @return int|false The last inserted ID, or false if not available.
     */
    public static function last_insert_id(): int|false
    {
        return self::$pdo->lastInsertId();
    }


    /**
     * Get a table instance by name.
     * @param string $table_name The name of the table.
     * @return SQLTable The table instance.
     */
    public static function get_table(string $table_name): SQLTable
    {
        return new SQLTable($table_name);
    }

    /**
     * Create a table with the given name and columns.
     * @param string $table The name of the table.
     * @param Column ...$columns The columns to create in the table.
     * @return bool Returns true if the table is created successfully.
     */
    public static function create_table(string $table, Column ...$columns): bool
    {
        $sql_query = "CREATE TABLE $table (";
        foreach ($columns as $column) {
            $additional = '';
            switch ($column->type) {
                case 'int':
                    $var_type = "INT(255)";
                    break;
                case 'string':
                    $var_type = "VARCHAR(255)";
                    break;
                case 'bool':
                    $var_type = "BOOL";
                    break;
                case 'float':
                    $var_type = "FLOAT(24)";
                    break;

                default:
                    DatabaseError::trigger("$column->type as VAR_TYPE is not supported.");
            }
            if ($column->timestamp) {
                $var_type = 'TIMESTAMP';
                $additional .= "DEFAULT CURRENT_TIMESTAMP ";
            }
            if (!$column->nullable) {
                $additional .= "NOT NULL";
            }
            $sql_query .= "$column->name $var_type $additional, ";
        }
        $sql_query = trim($sql_query, ", ") . ")";
        self::unsafe_query($sql_query);

        return true;
    }

    /**
     * Check if a table exists in the database.
     * @param string $name The name of the table.
     * @return bool Returns true if the table exists, false otherwise.
     */
    public static function table_exists(string $name): bool
    {
        $sql_query = "SHOW TABLES LIKE '$name'";
        self::unsafe_query($sql_query);
        return (bool) self::get_queried_data();
    }

    /**
     * Get the result of the last query.
     * @param bool $clean_array If true, returns a cleaned array of results.
     * @return mixed The query result.
     */
    protected static function get_queried_data(bool $clean_array = false): mixed
    {
        $mode = \PDO::FETCH_ASSOC;
        $query_result = self::$result->fetchAll($mode);
        if ($clean_array) {
            return static::clean_array($query_result);
        }
        return $query_result;
    }

    /**
     * Execute the given SQL statement.
     * @param AbstractStatement $sql_statement The SQL statement to execute.
     * @return bool Returns true if the query is successful, false otherwise.
     */
    protected static function execute_query(AbstractStatement $sql_statement): bool
    {
        try {
            self::$result = self::$pdo->query($sql_statement);
        } catch (\Throwable $t) {
            return false;
        }
        return true;
    }

    /**
     * Execute an unsafe SQL query (no prepared statements).
     * @param string $sql_statement The SQL query string.
     * @return bool Returns true if the query executes successfully, false otherwise.
     */
    private static function unsafe_query(string $sql_statement): bool
    {
        try {
            self::$result = self::$pdo->query($sql_statement);
        } catch (\Throwable $t) {
            return false;
        }
        return true;
    }
}
