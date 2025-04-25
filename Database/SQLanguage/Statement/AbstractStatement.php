<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError as Error;
use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\SyntaxCheck;


/**
 * Class AbstractStatement
 *
 * Represents an abstract SQL statement (e.g., SELECT, INSERT) with core functionality
 * for executing statements and defining tables and columns.
 * Dependent on the `Database` class for execution and `SyntaxCheck` for validation.
 */
abstract class AbstractStatement implements \Stringable
{
    /**
     * @var string The name of the table this statement operates on.
     */
    public readonly string $table;

    /**
     * @var string A comma-separated string of the specified columns.
     */
    public string $columns_string = '';

    /**
     * @var array The list of specified column names.
     */
    public array $columns = [];

    /**
     * @var bool Indicates if the statement has a result after execution.
     */
    private bool $has_result = false;

    /**
     * Converts the statement to its SQL string representation.
     *
     * @return string The SQL representation of the statement.
     */
    abstract public function __toString(): string;

    /**
     * Executes the statement on the current database connection.
     *
     * To retrieve results, use `Database::get_query_result()`.
     * @param bool $ignore_error Ignore SQL or Database errors
     * @return bool True if the execution was successful; otherwise, false.
     */
    public function execute(bool $ignore_error = false): bool
    {
        try {
            $result = Database::query($this);
            return $result;
        } catch (\Throwable $e) {
            if ($ignore_error) {
                return false;
            }
            $query = (string) $this;
            throw new Error("Could not execute '{$query}': $e");
        }
    }

    /**
     * Sets the table name for the SQL statement.
     *
     * @param string $table The name of the table to be used.
     *
     * @return static Instance of the current statement for method chaining.
     */
    protected function set_table(string $table): static
    {
        SyntaxCheck::is_table_name($table);
        $this->table = $table;
        return $this;
    }

    /**
     * Specifies the columns to be included in the SQL statement.
     *
     * @param array $columns An array of column names.
     *
     * @return static Instance of the current statement for method chaining.
     */
    protected function set_columns(array $columns): static
    {
        foreach ($columns as $column) {
            SyntaxCheck::is_field_name($column);
            $this->columns[] = $column;
            $this->columns_string .= "{$column}, ";
        }
        $this->columns_string = rtrim($this->columns_string, ', ');
        return $this;
    }
}
