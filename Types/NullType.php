<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\PrimitiveTypeStringRepresentationTrait;

class NullType extends AbstractType
{

    use PrimitiveTypeStringRepresentationTrait;
    protected static function get_php_type(): string
    {
        return 'NULL';
    }

    protected function to_string(): string
    {
        return "";
    }
}
