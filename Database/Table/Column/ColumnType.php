<?php

namespace PHP_Library\Database\Table\Column;

use PHP_Library\ClassTraits\MirrorTrait;
use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\ColumnType\Boolean;
use PHP_Library\Database\Table\Column\ColumnType\FloatingPoint;
use PHP_Library\Database\Table\Column\ColumnType\Integer;
use PHP_Library\Database\Table\Column\ColumnType\Text;
use PHP_Library\DatabaseModel\DatabaseModel;
use PHP_Library\Error\Error;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Class Column
 *
 * Represents a database table column with attributes like name, type, length, and constraints.
 *
 * Dependencies:
 * - SyntaxCheck: For validating column names against SQL syntax rules.
 * - SQLanguageError: For handling SQL syntax-related errors.
 */
abstract class ColumnType
{
    use MirrorTrait;

    abstract public function get_sql_type(): string;
    abstract public function get_php_type(): string;
    abstract public function __construct();
    public bool $nullable;

    public function is(string $type): bool
    {
        return strtolower(static::get_class_name()) == strtolower($type);
    }

    /**
     * Retrieves the column type for a given property.
     *
     * @param ReflectionProperty $property The property to check.
     * @return ColumnType The type of the column associated with the property.
     * @throws Error If the column type is unsupported.
     */
    public static function create_from_reflection_property(ReflectionProperty $property): ColumnType
    {
        $type = $property->getType();
        $type_name = $type->getName();
        if ($type === null)
        {
            $class_name = $property->getDeclaringClass()->getShortName();
            throw new Error("$class_name->{$type_name}'s is undefined. Can not initialize DatabaseModel.");
        }
        if (! $type instanceof ReflectionNamedType)
        {
            $class_name = $property->getDeclaringClass()->getShortName();
            throw new Error("$class_name->{$type_name}: Only Properties of a single type are supported for DatabaseModels.");
        }
        if (!$type->isBuiltin())
        {
            if (!is_a($type->getName(), DatabaseModel::class, true))
            {
                $class_name = $property->getDeclaringClass()->getShortName();
                throw new Error("$class_name->{$type_name}: is not a DatabaseModel.");
            }
            $type_name = 'int';
        }
        switch ($type_name)
        {
            case 'string':
                $column_type = new Text();
                break;
            case 'integer':
                $column_type = new Integer();
                break;
            case 'int':
                $column_type = new Integer();
                break;
            case 'float':
                $column_type = new FloatingPoint();
                break;
            case 'bool':
                $column_type = new FloatingPoint();
                break;
            default:
                throw new Error("'{$type_name}' is missing...");
        }
        $column_type->nullable = $type->allowsNull();
        return $column_type;
    }

    public static function create_from_string(string $type = 'string', bool $nullable = false): static
    {
        switch ($type)
        {
            case 'string':
                $column_type = new Text();
                $column_type->nullable = $nullable;
                return $column_type;
            case 'int':
                $column_type = new Integer();
                $column_type->nullable = $nullable;
                return $column_type;
            case 'float':
                $column_type = new FloatingPoint();
                $column_type->nullable = $nullable;
                return $column_type;
            case 'bool':
                $column_type = new Boolean();
                $column_type->nullable = $nullable;
                return $column_type;
            default:
                throw new Error("Can not create type by string. Type '{$type}' unknown.");
        }
    }
}
