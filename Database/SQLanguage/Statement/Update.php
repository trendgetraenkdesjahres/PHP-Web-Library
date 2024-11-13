<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Class Update
 *
 * Represents a UPDATE statement builder.
 * Build Statement with `where()` and `set()`.
 */
class Update extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    private string $set = "SET ";

    public array $update_cells = [];

    /**
     * Insert constructor.
     *
     * Constructs a Update statement. Convertible to a string.
     *
     * @param string $table The name of the table or an instance of AbstractTable
     * @throws \InvalidArgumentException If the provided table or column names are invalid
     */
    public function __construct(string $table)
    {
        $this->set_table($table);
    }

    public function set($column, $value): self
    {
        SyntaxCheck::is_field_name($column);
        SyntaxCheck::is_safe_value($value);

        if ($this->set !== "SET ") {
            $this->set .= ', ';
        }
        $this->update_cells[$column] = $value;
        $value = is_string($value) ? "'$value'" : $value;
        $this->set .= "{$column} = {$value}";
        return $this;
    }

    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "DELETE FROM {$this->table} {$this->get_where_clause()};";
    }
}
