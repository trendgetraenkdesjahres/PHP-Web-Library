<?php

namespace PHP_Library\ClassTraits;

use ReflectionClass;
use ReflectionProperty;

trait MirrorTrait
{
    public static function get_class_name(bool $short = true): string
    {
        $reflection = new ReflectionClass(get_called_class());
        if ($short) {
            return $reflection->getShortName();
        }
        return $reflection->getName();
    }

    /**
     * Retrieves the public properties of the class.
     *
     * @return ReflectionProperty[] Array of ReflectionProperty objects representing public properties.
     */
    public static function get_public_properties(): array
    {
        $class = new ReflectionClass(get_called_class());
        $public_properties =  array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            function ($property) {
                return ! $property->isStatic();
            }
        );
        return $public_properties;
    }

    /**
     * @return static Creates a prototype object without invoking the constructor. BAD PRACTICE
     * */
    public static function get_proto_object(): static
    {
        $class = new ReflectionClass(get_called_class());
        return $class->newInstanceWithoutConstructor();
    }
}
