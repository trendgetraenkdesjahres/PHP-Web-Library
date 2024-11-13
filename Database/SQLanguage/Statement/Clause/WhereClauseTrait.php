<?php

namespace PHP_Library\Database\SQLanguage\Statement\Clause;

use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Trait WhereClauseTrait
 *
 * Provides methods for constructing SQL WHERE clauses dynamically.
 * It allows building WHERE clauses for SELECT, UPDATE, DELETE, and other SQL statements.
 */
trait WhereClauseTrait
{
    /**
     * Constructs an array of where clause objects.
     * The keys are "WHERE", "AND_1", "AND_2", "OR_3" ...
     *
     * @var array
     */
    public array $where_objs = [];

    /**
     * The constructed WHERE clause
     * @var string
     **/
    private string $where_clause = 'WHERE ';

    /**
     * Flag indicating if the WHERE clause has been completed
     * @var bool
     **/
    private bool $where_clause_completed = true;

    /**
     * Constructs a WHERE clause for equality.
     *
     * @param string $column The column name for equality comparison
     * @param string|int $value The value to compare for equality
     * @return $this
     */
    public function where_equals(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '=', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} = {$value}");
    }

    /**
     * Constructs a WHERE clause for not equal comparison.
     *
     * @param string $column The column name for not equal comparison
     * @param string|int $value The value to compare for not equal
     * @return $this
     */
    public function where_not_equals(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '<>', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} <> {$value}");
    }

    /**
     * Constructs a WHERE clause for greater than comparison.
     *
     * @param string $column The column name for greater than comparison
     * @param string|int $value The value to compare for greater than
     * @return $this
     */
    public function where_greater_than(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '>', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} > {$value}");
    }

    /**
     * Constructs a WHERE clause for greater than or equal comparison.
     *
     * @param string $column The column name for greater than or equal comparison
     * @param string|int $value The value to compare for greater than or equal
     * @return $this
     */
    public function where_greater_or_equal(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '>=', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} >= {$value}");
    }

    /**
     * Constructs a WHERE clause for less than comparison.
     *
     * @param string $column The column name for less than comparison
     * @param string|int $value The value to compare for less than
     * @return $this
     */
    public function where_less_than(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '<', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} < {$value}");
    }

    /**
     * Constructs a WHERE clause for less than or equal comparison.
     *
     * @param string $column The column name for less than or equal comparison
     * @param string|int $value The value to compare for less than or equal
     * @return $this
     */
    public function where_less_than_or_equal(string $column, string|int $value): static
    {
        $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, '<=', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        return $this->append_where_clause("{$column} <= {$value}");
    }

    /**
     * Constructs a WHERE clause for BETWEEN comparison.
     *
     * @param string $column The column name for BETWEEN comparison
     * @param string|int $value1 The starting value for BETWEEN comparison
     * @param string|int $value2 The ending value for BETWEEN comparison
     * @return $this
     */
    public function where_between(string $column, string|int $value1, string|int $value2): static
    {
        return $this
            ->validate_where_values($column, $value1, $value2)
            ->add_where_clause_obj($column, '<>', $value1, $value2);
        $value1 = is_string($value1) ? "'$value1'" : $value1;
        $value2 = is_string($value2) ? "'$value2'" : $value2;
        $this->append_where_clause("{$column} BETWEEN {$value1} AND {$value2}");
    }

    /**
     * Constructs a WHERE clause for LIKE comparison.
     *
     * @param string $column The column name for LIKE comparison
     * @param string|int $value The value to compare for LIKE
     * @return $this
     */
    public function where_like(string $column, string|int $value): static
    {
        return $this
            ->validate_where_values($column, $value)
            ->add_where_clause_obj($column, 'LIKE', $value);
        $value = is_string($value) ? "'{$value}'" : $value;
        $this->append_where_clause("{$column} LIKE {$value}");
    }

    /**
     * Constructs a WHERE clause for IN comparison.
     *
     * @param string $column The column name for IN comparison
     * @param string|int ...$values The values for IN comparison
     * @return $this
     */
    public function where_in(string $column, string|int ...$values): static
    {
        $this->validate_where_values($column, ...$values)
            ->add_where_clause_obj($column, 'LIKE', $values);
        $value_string = '';
        foreach ($values as $key => $value) {
            $value_string .= is_string($value_string) ? "'{$value}'" : $value;
            if ($key !== array_key_last($values)) {
                $value_string .= ", ";
            }
        }
        return $this->append_where_clause("{$column} IN {$values}");
    }

    /**
     * Appends the logical OR operator to the WHERE clause.
     * Throws an error if the WHERE clause is empty or incomplete.
     *
     * @return $this
     * @throws SQLanguageError If the WHERE clause is empty or incomplete
     */
    public function or(): static
    {
        if ($this->where_clause === 'WHERE ') {
            throw new SQLanguageError("Can not append 'OR' operator to empty where clause.");
        }
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Can not append 'OR' operator to incomplete where clause.");
        }
        return $this->append_where_clause("OR", false);
    }

    /**
     * Appends the logical AND operator to the WHERE clause.
     * Throws an error if the WHERE clause is empty or incomplete.
     *
     * @return $this
     * @throws SQLanguageError If the WHERE clause is empty or incomplete
     */
    public function and(): static
    {
        if ($this->where_clause === 'WHERE ') {
            throw new SQLanguageError("Can not append 'AND' operator to empty where clause.");
        }
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("Can not append 'AND' operator to incomplete where clause.");
        }
        return $this->append_where_clause("AND", false);
    }

    /**
     * Gets the constructed WHERE clause.
     *
     * @return string The constructed WHERE clause.
     * @throws SQLanguageError If the WHERE clause is incomplete.
     */
    public function get_where_clause(): string
    {
        if (!$this->where_clause_completed) {
            throw new SQLanguageError("WHERE Clause incomplete.");
        }
        if ($this->where_clause == "WHERE ") {
            return '';
        }
        return $this->where_clause;
    }

    /**
     * Constructs an array of where clause objects.
     * The keys are "WHERE", "AND_1", "AND_2", "OR_3" ...
     *
     * @return WhereClauseCondition[] WHERE Objects.
     * @throws SQLanguageError If the WHERE Objects emtpy.
     */
    public function get_where_objs(): array
    {
        if (!$this->where_objs) {
            throw new SQLanguageError("WHERE Objects empty. Be sure to initiate Database before using Statement class.");
        }
        return $this->where_objs;
    }

    /**
     * Validates the input values for constructing the WHERE clause.
     * Throws an error if the WHERE clause is already set or if the input values are improper for SQL.
     *
     * @param string $column The column name for the WHERE clause
     * @param string|int ...$values The values for the WHERE clause
     * @return $this
     * @throws SQLanguageError If the WHERE clause is already completed
     */
    private function validate_where_values(string $column, string|int ...$values): static
    {
        if ($this->where_clause !== 'WHERE ' && $this->where_clause_completed) {
            throw new SQLanguageError("Where clause already completed.");
        }
        SyntaxCheck::is_field_name($column);
        foreach ($values as $value) {
            SyntaxCheck::is_safe_value($value);
        }
        return $this;
    }

    /**
     * Appends the provided string to the WHERE clause.
     * Updates the $where_clause_completed flag accordingly.
     *
     * @param string $string The string to append to the WHERE clause
     * @param bool $clause_is_complete Flag indicating if the WHERE clause is complete after appending the string
     * @return $this
     */
    private function append_where_clause(string $string, bool $clause_is_complete = true): static
    {
        $this->where_clause .= trim($string) . " ";
        $this->where_clause_completed = $clause_is_complete;
        return $this;
    }

    /**
     * Helper for FileDatabase queries...
     * The keys are "WHERE", "AND_1", "AND_2", "OR_3" ...
     * @return  static
     */
    private function add_where_clause_obj(string $lhs, string $operator, mixed $rhs, mixed $rhs2 = null): static
    {
        if (Database::get_type() !== 'FileDatabase') {
            return $this;
        }
        if (empty($this->where_objs)) {
            $this->where_objs['WHERE'] = new WhereClauseCondition($lhs, $operator, $rhs, $rhs2);
            return $this;
        }
        if (str_ends_with($this->where_clause, 'AND')) {
            $this->where_objs['AND_' . count($this->where_objs)] = new WhereClauseCondition($lhs, $operator, $rhs, $rhs2);
            return $this;
        }
        if (str_ends_with($this->where_clause, 'OR')) {
            $this->where_objs['OR_' . count($this->where_objs)] = new WhereClauseCondition($lhs, $operator, $rhs, $rhs2);
            return $this;
        }
    }
}
