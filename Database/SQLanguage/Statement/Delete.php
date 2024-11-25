<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;


/**
 * Class Delete
 *
 * Represents a DELETE SQL statement.
 * The statement can be constructed using the `where()` method provided by the `WhereClauseTrait`.
 * Depends on `SQLanguageError` for error handling.
 */
class Delete extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    /**
     * Constructs a new Delete statement.
     *
     * @param string $table The name of the table from which rows will be deleted.
     */
    public function __construct(string $table)
    {
        $this->set_table($table);
    }

    /**
     * Converts the Delete statement to its SQL string representation.
     *
     * @return string The SQL DELETE statement as a string.
     *
     * @throws SQLanguageError If the statement is incomplete (e.g., missing a WHERE clause).
     */
    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "DELETE FROM {$this->table} {$this->get_where_clause()};";
    }
}
