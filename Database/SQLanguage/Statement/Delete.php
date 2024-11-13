<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;

/**
 * Class Delete
 *
 * Represents a DELETE statement.
 * Build Statement with `where()`.
 */
class Delete extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    /**
     * Insert constructor.
     *
     * Constructs a Delete statement. Convertible to a string.
     *
     * @param string $table The name of the table.
     */
    public function __construct(string $table)
    {
        $this->set_table($table);
    }

    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "DELETE FROM {$this->table} {$this->get_where_clause()};";
    }
}
