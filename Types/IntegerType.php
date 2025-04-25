<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\PrimitiveTypeStringRepresentationTrait;

class IntegerType extends AbstractType
{
    use PrimitiveTypeStringRepresentationTrait;
    protected static function get_php_type(): string
    {
        return 'integer';
    }

    protected function to_string(): string
    {
        return (string) $this->value;
    }
}
