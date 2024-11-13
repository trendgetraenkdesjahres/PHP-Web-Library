<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\Database;
use PHP_Library\Database\Error\DatabaseError;
use PHP_Library\Database\SQLanguage\Statement\Delete;
use PHP_Library\Database\SQLanguage\Statement\Insert;
use PHP_Library\Database\SQLanguage\Statement\Select;
use PHP_Library\Database\SQLanguage\Statement\Update;
use PHP_Library\Settings\Settings;

abstract class DataTable
{
    /**
     * Holds the initialized table instance implementation. It can not be this abstract class DataTable, that will not work.
     *
     * @var DataTable|null
     */
    private static ?DataTable $instance = null;

    /**
     * The name of this table.
     *
     * @var string
     */
    public string $name;

    /**
     * Create a DB Statement for this table. To query it on the current DB, call `execute()`.
     * Build Statement with `where()`.
     *
     * @return Delete Statement
     **/
    public function delete(): Delete
    {
        return new Delete($this->name);
    }

    /**
     * Delete a row of the table.
     * Values need to be in order of the columns.
     *
     * @param int $id The ID of the row.
     * @return bool Success.
     */
    public function delete_row(int $id): bool
    {
        $this
            ->delete()
            ->where_equals('id', $id)
            ->execute();
        return (bool) Database::get_query_result();
    }

    /**
     * Create a DB Statement for this table. To query it on the current DB, call `execute()`.
     * Build Statement with `values()`.
     *
     * @return Insert Statement
     **/
    public function insert(string ...$columns): Insert
    {
        return new Insert($this->name, ...$columns);
    }

    /**
     * Inser a new row into the table.
     * Values need to be in order of the columns.
     *
     * @param string|int|float $values Values in the order of the columns.
     * @return int Count of inserted cells.
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
     * Alias for `insert_row()`
     *
     * @param string|int|float $values Values in the order of the columns.
     * @return int Count of inserted cells.
     */
    public function add_row(string|int|float ...$values): int
    {
        return $this->insert_row(...$values);
    }

    /**
     * Create a DB Statement for this table. To query it on the current DB, call `execute()`.
     * Build Statement with `where()`.
     *
     * @return Select Statement
     **/
    public function select(string $column = '*', string ...$more_columns): Select
    {
        return new Select($this->name, $column, ...$more_columns);
    }

    /**
     * Select and get a row from the table by its ID.
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    public function select_row(int $id): array
    {
        $this
            ->select()
            ->where_equals('id', $id)
            ->execute();
        return Database::get_query_result()[0];
    }

    /**
     * Alias for `select_row()`
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    public function get_row(int $id): array
    {
        return $this->select_row($id);
    }

    /**
     * Create a DB Statement for this table. To query it on the current DB, call `execute()`.
     * Build Statement with `where()` and `set()`.
     *
     * @return Update Statement
     **/
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
        $update = $this->update()
            ->where_equals('id', $id);
        foreach ($cells as $column_name => $value) {
            $update->set($column_name, $value);
        }
        $update->execute();
        return Database::get_query_result();
    }

    final public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the table.
     *
     * @return string The name.
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Factory method to get or initialize the appropriate storage instance.
     *
     * @return static The initialized storage instance.
     * @throws \Error If no suitable configuration for Filebased or DBbased setting is found.
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
