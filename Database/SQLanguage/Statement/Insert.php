<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;

/**
 * Class Insert
 *
 * Represents a INSETRT statement.
 * Build Statement with `values()`.
 */
class Insert extends AbstractStatement
{
    use Clause\ValuesClauseTrait;
    /**
     *
     * Constructs a Insert statement.
     * @param string $table The name of the table.
     */
    public function __construct(string $table, string ...$columns)
    {
        $this->set_table($table);
        if ($columns) {
            $this->set_columns($columns);
        }
    }

    /**
     * @return string
     * @throws \Error If the VALUES clause is not completed.
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
