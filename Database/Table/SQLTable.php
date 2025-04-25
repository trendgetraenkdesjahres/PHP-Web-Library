<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\SQLDatabase;
use PHP_Library\Settings\Settings;

/**
 * Class SQLTable
 *
 * Represents a table in an SQL-based database. Extends the generic DataTable class to provide
 * SQL-specific operations for interacting with relational databases.
 *
 * Dependencies:
 * - PHP_Library\Database\SQLDatabase: For accessing the SQL database connection and executing queries.
 */
class SQLTable extends DataTable
{

    public function get_primary_key(): string
    {
        $databasename = Settings::get('Database/database_name', true);
        return (string) static::get_instance('INFORMATION_SCHEMA.COLUMNS')
            ->select('COLUMN_NAME')
            ->where_equals('TABLE_SCHEMA', $databasename)
            ->and()
            ->where_equals('TABLE_NAME', $this->name)
            ->and()
            ->where_equals('COLUMN_KEY', 'PRI')
            ->get();
    }
    /**
     * Counts the number of rows in the table.
     *
     * @return int The number of rows in the table.
     */
    public function select_count(): int
    {
        return SQLDatabase::$pdo->query("SELECT count(*) FROM {$this->name}")->fetchColumn();
    }
}
