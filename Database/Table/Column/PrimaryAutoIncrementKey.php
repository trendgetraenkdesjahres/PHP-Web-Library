<?php

namespace PHP_Library\Database\Table\Column;

// AUTO INCREMENT
class PrimaryAutoIncrementKey extends PrimaryKey
{
    public static bool $auto_increment = true;
    public function __construct(string $name)
    {
        parent::__construct($name, 'int');
    }
}
