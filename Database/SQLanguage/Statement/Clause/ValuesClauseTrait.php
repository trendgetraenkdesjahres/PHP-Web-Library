<?php

namespace PHP_Library\Database\SQLanguage\Statement\Clause;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Values WhereClauseTrait
 *
 * Provides methods for constructing SQL VALUE clauses dynamically.
 */
trait ValuesClauseTrait
{
    /**
     * The constructed VALUES clause
     * @var string
     **/
    public string $values_clause = '';

    /**
     * The list of single VALUES
     * @var array
     **/
    public array $values = [];

    /**
     * Flag indicating if the VALUES clause has been completed
     * @var bool
     **/
    private bool $values_clause_completed = false;

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
     * Gets the constructed VALUES clause.
     *
     * @return string The constructed VALUES clause.
     * @throws SQLanguageError If the VALUES clause is incomplete.
     */
    protected function get_values_clause(): string
    {
        if (!$this->values_clause_completed) {
            throw new SQLanguageError("VALUES Clause incomplete.");
        }
        return $this->values_clause;
    }
}
