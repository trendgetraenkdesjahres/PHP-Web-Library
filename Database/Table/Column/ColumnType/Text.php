<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class Text extends ColumnType
{
    public function __construct() {}

    public function get_sql_type(): string
    {
        return "TEXT";
    }

    public function get_php_type(): string
    {
        return "string";
    }
}
