<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\PrimitiveTypeStringRepresentationTrait;

class BooleanType extends AbstractType
{
    use PrimitiveTypeStringRepresentationTrait;
    protected static function get_php_type(): string
    {
        return 'boolean';
    }

    protected function to_string(): string
    {
        return $this->value ? "true" : "false";
    }
}
