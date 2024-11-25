<?php

namespace PHP_Library\Database\SQLanguage\Statement\Clause;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Trait ValuesClauseTrait
 *
 * Provides methods for constructing and managing SQL VALUES clauses dynamically.
 * Handles appending values, validating inputs, and generating the final VALUES clause string.
 */
trait ValuesClauseTrait
{
    /**
     * @var string The constructed VALUES clause as a string.
     */
    public string $values_clause = '';

    /**
     * @var array The list of individual values to be included in the VALUES clause.
     */
    public array $values = [];

    /**
     * @var bool Flag indicating whether the VALUES clause is finalized.
     */
    private bool $values_clause_completed = false;

    /**
     * Adds values to the VALUES clause and marks it as completed.
     *
     * @param string|int|float ...$value A list of values to include in the clause. Strings are quoted automatically.
     * @return self The current instance for method chaining.
     *
     * @throws SQLanguageError If values are appended after the VALUES clause is marked as complete.
     * @throws \InvalidArgumentException If any value is invalid.
     */
    public function values(string|int|float ...$value): self
    {
        if ($this->values_clause_completed) {
            throw new SQLanguageError("Can not append more values to complete VALUES clause.");
        }
        foreach ($value as $value) {
            SyntaxCheck::is_safe_value($value);
            $this->values[] = $value;
            if (is_string($value)) {
                $value = "'{$value}'";
            }
            $this->values_clause .= "{$value}, ";
        }
        $this->values_clause = "VALUES (" . rtrim($this->values_clause, ', ') . ")";
        $this->values_clause_completed = true;
        return $this;
    }

    /**
     * Retrieves the constructed VALUES clause.
     *
     * @return string The finalized VALUES clause as a string.
     *
     * @throws SQLanguageError If the VALUES clause has not been completed.
     */
    protected function get_values_clause(): string
    {
        if (!$this->values_clause_completed) {
            throw new SQLanguageError("VALUES Clause incomplete.");
        }
        return $this->values_clause;
    }
}
