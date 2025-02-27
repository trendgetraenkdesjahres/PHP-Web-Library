<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class FloatingPoint extends ColumnType
{
    private int $precision;

    public function __construct(int $p = 2)
    {
        $this->precision = $p;
    }

    public function get_sql_type(): string
    {
        return "FLOAT({$this->precision})";
    }

    public function get_php_type(): string
    {
        return "float";
    }
}
