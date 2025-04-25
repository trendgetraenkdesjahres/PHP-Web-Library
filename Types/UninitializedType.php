<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\PrimitiveTypeStringRepresentationTrait;

class UninitializedType extends AbstractType
{
    const SYMBOL = "\u{E666}";

    use PrimitiveTypeStringRepresentationTrait;
    protected static function get_php_type(): string
    {
        return 'uninitialized';
    }

    protected static function validate_type($value): bool
    {
        return  true;
    }

    protected function to_string(): string
    {
        return "";
    }
}
