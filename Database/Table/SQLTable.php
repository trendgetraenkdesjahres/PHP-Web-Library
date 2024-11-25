<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\SQLDatabase;

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
