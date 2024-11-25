<?php

namespace PHP_Library\Database\SQLanguage\Statement\Clause;

/**
 * Class WhereClauseCondition
 *
 * Represents a condition in an SQL WHERE clause.
 * Used to define the left-hand side (LHS), operator, and right-hand side (RHS) values,
 * with optional support for BETWEEN conditions.
 */
class WhereClauseCondition
{
    /**
     * @var string The operator used in the condition (e.g., '=', 'LIKE', 'BETWEEN').
     */
    public string $operator;

    /**
     * @var string The left-hand side (LHS) of the condition (e.g., column name).
     */
    public string $lhs;

    /**
     * @var mixed The right-hand side (RHS) value of the condition (e.g., value to compare).
     */
    public mixed $rhs;

    /**
     * @var mixed|null The second value for conditions like BETWEEN, or null if not applicable.
     */
    public mixed $rhs2;

    /**
     * Constructor for WhereClauseCondition.
     *
     * @param string $lhs The left-hand side (LHS) of the condition.
     * @param string $operator The operator to use in the condition (e.g., '=', 'BETWEEN').
     * @param mixed $rhs The right-hand side (RHS) value.
     * @param mixed|null $rhs2 Optional second value for operators like 'BETWEEN'.
     */
    public function __construct(string $lhs, string $operator, mixed $rhs, mixed $rhs2 = null)
    {
        $this->operator = $operator;
        $this->lhs = $lhs;
        $this->rhs = $rhs;
        if ($operator === 'BETWEEN') {
            $this->rhs2 = $rhs2;
        }
    }
}
