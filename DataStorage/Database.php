<?php

namespace DataStorage;

use Notices\Warning;
use Settings\Settings;
use PDO;
use PDOStatement;

/**
 * DatabaseStorage is a class that handles database storage using PDO.
 */
class DatabaseStorage implements DataStorageInterface
{
    private static $pdo;
    private static PDOStatement $result;

    /**
     * Initialize the database connection.
     *
     * @return bool Returns true if initialization is successful, false otherwise.
     */
    public static function initalize(): bool
    {
        $databasename = Settings::get('db_name');
        $username = Settings::get('db_username');
        $password = Settings::get('db_password');
        $host = ($host = Settings::get('db_host')) ? $host : 'localhost';
        $port = ($port = Settings::get('db_port')) ? $port : '3306';
        $charset = ($charset = Settings::get('db_charset')) ? $charset : 'utf8mb4';
        $driver = ($driver = Settings::get('db_driver')) ? $driver : 'mysql';

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
            self::$pdo = new PDO(
                $dns,
                $username,
                $password,
                $options
            );
        } catch (\Throwable $e) {
            Warning::trigger("$dns: $e->message");
            return false;
        }
        return true;
    }

    /**
     * Create a table with the given name and columns.
     *
     * @param string $table The name of the table.
     * @param TableColumn ...$columns The columns to create.
     */
    public static function create_table(string $table, TableColumn ...$columns)
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
                    throw new \Error("$column->type as VAR_TYPE is not supported.");
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
        self::query($sql_query);
    }

    /**
     * Get queried data with the specified fetch mode.
     *
     * @param int $mode The PDO fetch mode.
     * @return mixed The queried data.
     */
    public static function get_queried_data(int $mode = PDO::FETCH_DEFAULT): mixed
    {
        return self::$result->fetchAll($mode);
    }


    /**
     * Execute a SQL query.
     *
     * @param string $sql_query The SQL query to execute.
     */
    public static function query(string $sql_query)
    {
        try {
            self::$result = self::$pdo->query($sql_query);
        } catch (\Throwable $e) {
            Warning::trigger($e->getMessage());
        }
    }

    /**
     * Get a table instance by name.
     *
     * @param string $table_name The name of the table.
     * @return DatabaseTable The table instance.
     */
    public static function get_table(string $table_name): DatabaseTable
    {
        return new DatabaseTable($table_name);
    }
}
