<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Class Update
 *
 * Represents a SQL UPDATE statement builder.
 * Allows constructing statements with `set()` to define column-value pairs and `where()` for conditional updates.
 * Depends on `WhereClauseTrait` for WHERE clause handling and `SyntaxCheck` for validation.
 */
class Update extends AbstractStatement
{
    use Clause\WhereClauseTrait;

    /**
     * @var array The mapping of columns to updated values.
     */
    public array $update_cells = [];

    /**
     * @var string The SET clause string for the statement.
     */
    private string $set = "SET ";

    /**
     * Constructs an Update statement. Convertible to a string.
     *
     * @param string $table The name of the table or an instance of AbstractTable.
     * @throws \InvalidArgumentException If the table name is invalid.
     */
    public function __construct(string $table)
    {
        $this->set_table($table);
    }

    /**
     * Adds a column to be unset (set to NULL).
     *
     * @param string $column The name of the column to unset.
     * @return Update The current instance for method chaining.
     */
    public function unset(string $column): self
    {
        return $this->set($column, null);
    }

    /**
     * Adds a column-value pair to the SET clause of the statement.
     *
     * @param string $column The column name.
     * @param mixed $value The value to set for the column. Strings are automatically quoted.
     * @return Update The current instance for method chaining.
     *
     * @throws \InvalidArgumentException If the column name or value is invalid.
     */
    public function set(string $column, $value): self
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

    /**
     * Converts the UPDATE statement into its SQL string representation.
     *
     * @return string The SQL UPDATE statement as a string.
     *
     * @throws SQLanguageError If the WHERE clause is incomplete.
     */
    public function __toString(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Statement is not completed.");
        }
        return "DELETE FROM {$this->table} {$this->get_where_clause()};";
    }
}
