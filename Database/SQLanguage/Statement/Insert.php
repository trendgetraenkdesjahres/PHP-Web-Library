<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;

/**
 * Class Insert
 *
 * Represents an SQL INSERT statement.
 * Allows building the statement using the `values()` method provided by `ValuesClauseTrait`.
 * Depends on `SQLanguageError` for error handling.
 */
class Insert extends AbstractStatement
{
    use Clause\ValuesClauseTrait;

    /**
     * Constructs a new Insert statement.
     *
     * @param string $table The name of the table where data will be inserted.
     * @param string ...$columns Optional column names to include in the INSERT statement.
     */
    public function __construct(string $table, string ...$columns)
    {
        $this->set_table($table);
        if ($columns) {
            $this->set_columns($columns);
        }
    }

    /**
     * Converts the Insert statement to its SQL string representation.
     *
     * @return string The SQL INSERT statement as a string.
     *
     * @throws SQLanguageError If the VALUES clause is incomplete.
     */
    public function __toString(): string
    {
        if (!$this->values_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        $columns = $this->columns_string ? "({$this->columns_string})" : '';
        return "INSERT INTO {$this->table} {$columns} {$this->get_values_clause()};";
    }
}
