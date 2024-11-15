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

    /** @var string The LIMIT clause */
    private string $limit = '';

    /** @var null|array The result, which will be filled be execute and __deconstruct() */
    private ?array $result = null;

    /**
     * Constructs a Select statement. Convertible to a string.
     *
     * @param string $table The name of the table.
     * @param string $column The first column to select (default is '*')
     * @param string ...$more_columns Additional columns to select
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
     * Executes this Statement on current DB and returns the result of the query.
     * if the result is an array, and has just one element, it will return this element (recursivly).
     * @return mixed Success
     */
    public function get(bool $clean = true): mixed
    {
        $this->execute();
        return Database::get_query_result($clean);
    }

    /**
     * Sets the LIMIT clause for the SELECT statement.
     *
     * @param int $limit The maximum number of rows to return
     * @return Select Instance of the current Select for method chaining.
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "SELECT {$this->columns_string} FROM {$this->table} {$this->get_where_clause()} {$this->limit};";
    }
}
