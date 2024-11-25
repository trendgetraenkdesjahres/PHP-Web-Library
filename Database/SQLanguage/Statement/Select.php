<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\Error\SQLanguageError;

/**
 * Class Select
 *
 * Represents a SELECT statement builder.
 * Build Statement with `where()`.
 */
class Select extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    /**
     * @var string The LIMIT clause as a string.
     */
    private string $limit = '';

    /**
     * @var null|array The result of the executed query, or null if not executed.
     */
    private ?array $result = null;

    /**
     * Constructs a Select statement. Convertible to a string.
     *
     * @param string $table The name of the table.
     * @param string $column The first column to select (default is '*').
     * @param string ...$more_columns Additional columns to select.
     */
    public function __construct(string $table, string $column = '*', string ...$more_columns)
    {
        $this->set_table($table);
        if ($column === '*') {
            $this->columns_string = '*';
        } else {
            $this->set_columns(array_merge([$column], $more_columns));
        }
    }

    /**
     * Executes the SELECT statement on the current database and retrieves the result.
     *
     * @param bool $clean Whether to clean the query result after retrieval:
     * If the result is an array with a single element, it returns that element recursively.
     * @return mixed The query result, or a single element if the result is a single-element array.
     */
    public function get(bool $clean = true): mixed
    {
        $this->execute();
        return Database::get_query_result($clean);
    }

    /**
     * Sets the LIMIT clause for the SELECT statement.
     *
     * @param int $limit The maximum number of rows to return.
     * @return Select The current instance for method chaining.
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Converts the SELECT statement into its SQL string representation.
     *
     * @return string The SQL SELECT statement as a string.
     *
     * @throws SQLanguageError If the WHERE clause is incomplete.
     */
    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "SELECT {$this->columns_string} FROM {$this->table} {$this->get_where_clause()} {$this->limit};";
    }
}
