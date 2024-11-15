<?php

namespace PHP_Library\Database\Table\Column;

// AUTO INCREMENT
class PrimaryKey extends Column
{
    public static bool $is_primary_key = true;
    public function __construct(string $name, string $type, ?int $length = null)
    {
        parent::__construct($name, $type, $length);
    }
}
