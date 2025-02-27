<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class Decimal extends ColumnType
{
    private int $size;
    private int $decimal_point;

    public function __construct(int $size = 65, int $d = 2)
    {
        $this->size = $size;
        $this->decimal_point = $d;
    }

    public function get_sql_type(): string
    {
        return "DECIMAL({$this->size},{$this->decimal_point})";
    }

    public function get_php_type(): string
    {
        return "float";
    }
}
