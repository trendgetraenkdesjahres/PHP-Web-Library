<?php

namespace  PHP_Library\Database;

use  PHP_Library\Settings\Settings;
use PDO;
use PDOStatement;
use PHP_Library\Database\SQLanguage\Statement\AbstractStatement;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\SQLTable;
use PHP_Library\Database\Error\DatabaseError;

/**
 * DatabaseStorage is a class that handles database storage using PDO.
 */
class SQLDatabase extends Database
{
    private static PDO $pdo;
    private static PDOStatement $result;

    /**
     * Initialize the database connection.
     *
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
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO(
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

    public static function last_insert_id(): int|false
    {
        return self::$pdo->lastInsertId();
    }

    /**
     * Get a table instance by name.
     *
     * @param string $table_name The name of the table.
     * @return SQLTable The table instance.
     */
    public static function get_table(string $table_name): SQLTable
    {
        return new SQLTable($table_name);
    }

    /**
     * Create a table with the given name and columns.
     *
     * @param string $table The name of the table.
     * @param TableColumn ...$columns The columns to create.
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
     * Check if table exists
     *
     * @param string $name The name of the table.
     * @return bool
     */
    public static function table_exists(string $name): bool
    {
        $sql_query = "SHOW TABLES LIKE '$name'";
        self::unsafe_query($sql_query);
        return (bool) self::get_queried_data();
    }

    protected static function get_queried_data(bool $clean_array = false): mixed
    {
        $mode = PDO::FETCH_ASSOC;
        $query_result = self::$result->fetchAll($mode);
        if ($clean_array) {
            return static::clean_array($query_result);
        }
        return $query_result;
    }

    protected static function execute_query(AbstractStatement $sql_statement): bool
    {
        try {
            self::$result = self::$pdo->query($sql_statement);
        } catch (\Throwable $t) {
            return false;
        }
        return true;
    }

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
