<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class VarChar extends ColumnType
{
    private int $size;

    public function __construct(int $size = 255)
    {
        $this->size = $size;
    }

    public function get_sql_type(): string
    {
        return "VARCHAR({$this->size})";
    }

    public function get_php_type(): string
    {
        return "string";
    }
}
