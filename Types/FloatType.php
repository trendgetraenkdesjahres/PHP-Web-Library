<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\PrimitiveTypeStringRepresentationTrait;

class FloatType extends AbstractType
{
    use PrimitiveTypeStringRepresentationTrait;
    protected static function get_php_type(): string
    {
        return 'double';
    }

    protected function to_string(): string
    {
        return (string) $this->value;
    }
}
