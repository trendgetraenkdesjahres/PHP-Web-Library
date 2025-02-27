<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class Integer extends ColumnType
{
    private bool $unsigned;

    public function __construct(bool $unsigned = false)
    {
        $this->unsigned = $unsigned;
    }

    public function get_sql_type(): string
    {
        return "INT" . $this->unsigned ? " UNSIGNED" : "";
    }

    public function get_php_type(): string
    {
        return "int";
    }
}
