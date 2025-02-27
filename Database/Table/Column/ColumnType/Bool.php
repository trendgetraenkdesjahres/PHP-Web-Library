<?php

namespace PHP_Library\Database\Table\Column\ColumnType;

use PHP_Library\Database\Table\Column\ColumnType;

class Boolean extends ColumnType
{

    public function __construct() {}

    public function get_sql_type(): string
    {
        return "BOOLEAN";
    }

    public function get_php_type(): string
    {
        return "bool";
    }
}
