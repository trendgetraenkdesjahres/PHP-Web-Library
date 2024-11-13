<?php

namespace PHP_Library\Database\SQLanguage\Statement\Clause;

class WhereClauseCondition
{
    public string $operator;
    public string $lhs;
    public mixed $rhs;
    public mixed $rhs2;

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
