<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\Database;
use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\SQLanguage\Statement\Delete;
use PHP_Library\Database\SQLanguage\Statement\Insert;
use PHP_Library\Database\SQLanguage\Statement\Select;
use PHP_Library\Database\SQLanguage\Statement\Update;
use PHP_Library\Settings\Settings;

/**
 * Abstract class representing a database table.
 * Provides methods for CRUD operations and SQL statement generation.
 *
 * This class depends on:
 * - PHP_Library\Database\Database for executing queries.
 * - PHP_Library\Database\SQLanguage\Statement classes for SQL operations.
 * - PHP_Library\Settings\Settings for configuration.
 */
abstract class DataTable
{
    /**
     * Holds the initialized table instance.
     * Must be an implementation of DataTable, not the abstract class itself.
     *
     * @var DataTable|null
     */
    private static ?DataTable $instance = null;

    /**
     * @var string
     */
    protected static string $primary_key;

    /**
     * The name of the database table.
     *
     * @var string
     */
    public string $name;

    public function display(): void
    {
        $table = static::get_instance($this->name)->select()->get(false);
        $header = array_keys($table[0]);
        array_unshift($table, $header);
        // find the longest element in each column
        foreach ($table as $row) {
            $column_index = 0;
            $longest_element_in_column = [];
            foreach ($row as $column_name => $cell) {
                if (!isset($longest_element_in_column[$column_index])) {
                    $longest_element_in_column[$column_index] = strlen((string) $cell);
                }
                if ($longest_element_in_column[$column_index] < strlen((string) $cell)) {
                    $longest_element_in_column[$column_index] = strlen((string) $cell);
                }
            }
            $column_index++;
        }
        //display
        foreach ($table as $row) {
            $column_index = 0;
            foreach ($row as $column_name => $cell) {
                echo str_pad((string)$cell, $longest_element_in_column[$column_index]), " ." . " ";
            }
            echo "\n";
            $column_index++;
        }
    }

    /**
     * Returns the column name of the primary key.
     *
     * @return string Primary Key of this table.
     */
    abstract public function get_primary_key(): string;

    /**
     * Returns the count of rows in the table.
     *
     * @return int Number of rows in the table.
     */
    abstract public function select_count(): int;

    /**
     * Creates a DELETE SQL statement for this table.
     * Execute the query using `execute()` and build conditions with `where()`.
     *
     * @return Delete SQL DELETE statement for the table.
     */
    public function delete(): Delete
    {
        return new Delete($this->name);
    }

    /**
     * Deletes a row in the table by its ID.
     *
     * @param int $id The ID of the row to delete.
     * @return bool Whether the deletion was successful.
     */
    public function delete_row(int $id): bool
    {
        $pimary_key = static::get_primary_key();
        $this
            ->delete()
            ->where_equals($pimary_key, $id)
            ->execute();
        return (bool) Database::get_query_result();
    }

    /**
     * Creates an INSERT SQL statement for this table.
     * Execute the query using `execute()` and add values with `values()`.
     *
     * @param string ...$columns The column names for the insert.
     * @return Insert SQL INSERT statement for the table.
     */
    public function insert(string ...$columns): Insert
    {
        return new Insert($this->name, ...$columns);
    }

    /**
     * Inserts a new row into the table.
     *
     * @param string|int|float ...$values Values to insert, in column order.
     * @return int Number of affected rows.
     */
    public function insert_row(string|int|float ...$values): int
    {
        $this
            ->insert()
            ->values(...$values)
            ->execute();
        return (int) Database::get_query_result();
    }

    /**
     * Alias for `insert_row()`.
     *
     * @param string|int|float ...$values Values to insert, in column order.
     * @return int Number of affected rows.
     */
    public function add_row(string|int|float ...$values): int
    {
        return $this->insert_row(...$values);
    }

    /**
     * Creates a SELECT SQL statement for this table.
     * Execute the query using `execute()` or `get()` and add conditions with `where()`.
     *
     * @param string $column The main column to select.
     * @param string ...$more_columns Additional columns to select.
     * @return Select SQL SELECT statement for the table.
     */
    public function select(string $column = '*', string ...$more_columns): Select
    {
        return new Select($this->name, $column, ...$more_columns);
    }

    /**
     * Selects a row from the table by its ID.
     *
     * @param int $id The ID of the row to select.
     * @return array Associative array representing the row data.
     */
    public function select_row(int $id): array
    {
        $pimary_key = static::get_primary_key();
        $this
            ->select()
            ->where_equals($pimary_key, $id)
            ->execute();
        return Database::get_query_result()[0];
    }

    /**
     * Alias for `select_row()`.
     *
     * @param int $id The ID of the row to select.
     * @return array Associative array representing the row data.
     */
    public function get_row(int $id): array
    {
        return $this->select_row($id);
    }

    /**
     * Creates an UPDATE SQL statement for this table.
     * Execute the query using `execute()` and set conditions with `where()` and `set()`.
     *
     * @return Update SQL UPDATE statement for the table.
     */
    public function update(): Update
    {
        return new Update($this->name);
    }

    /**
     * Update a row from the table by its ID.
     *
     * @param int $id The ID of the row.
     * @param array $cells Assotiative array with cells and values.
     * @return array The row data.
     */
    public function update_row(int $id, array $cells): array
    {
        $pimary_key = static::get_primary_key();
        $update = $this->update()
            ->where_equals($pimary_key, $id);
        foreach ($cells as $column_name => $value) {
            $update->set($column_name, $value);
        }
        $update->execute();
        if (Database::get_query_result()) {
            return $cells;
        }
        return [];
    }


    /**
     * Constructor to initialize the table with its name.
     *
     * @param string $name The name of the database table.
     */
    final public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Retrieves the name of the table.
     *
     * @return string The table name.
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Factory method to get or initialize the appropriate table instance.
     *
     * @param string $name The name of the table.
     * @return static The initialized table instance.
     * @throws DatabaseError If no configuration for File-based or DB-based storage is found.
     */
    protected static function get_instance(string $name): static
    {
        if (self::$instance === null) {
            // Check settings to decide the storage type
            if (Settings::get('Database/database_name')) {
                self::$instance = new SQLTable($name);
            } elseif (Settings::get('Database/file_name')) {
                self::$instance = new FileTable($name);
            } else {
                throw new DatabaseError("No setting for 'Database/database_name' or 'Database/file_name' found in settings-file.");
            }
        }
        return self::$instance;
    }
}
