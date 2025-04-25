<?php

namespace PHP_Library\Database\Table\Column;

use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\Error\SQLanguageError;
use PHP_Library\Database\SQLanguage\SyntaxCheck;
use PHP_Library\DatabaseModel\DatabaseModel;
use PHP_Library\Error\Error;
use ReflectionClass;
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
class Column
{
    /**
     * The name of the column.
     *
     * @var string
     */
    public string $name;


    /**
     * The type of the column.
     *
     * @var ColumnType
     */
    public ColumnType $type;

    /**
     * Indicates if the column has an auto-increment property.
     *
     * @var bool
     */
    public static bool $auto_increment = false;

    /**
     * Indicates if the column is a primary key.
     *
     * @var bool
     */
    public static bool $is_primary_key = false;

    /**
     * Constructor for the Column class.
     *
     * @param string $name The column name (validated against SQL syntax rules).
     * @param string|ColumnType $type The data type of the column (default: 'string').
     * @param int|null $length The maximum length of the column (optional).
     * @param bool $nullable Whether the column allows NULL values (default: false).
     * @param bool $timestamp Whether the column is of type timestamp (default: false).
     *
     * @throws SQLanguageError If the column name does not pass SQL syntax validation.
     */
    public function __construct(
        string $name,
        string|ColumnType $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public bool $timestamp = false
    )
    {
        if (! SyntaxCheck::is_field_name($name))
        {
            throw new SQLanguageError("{$name} is not a column name.");
        }
        if (is_string($type))
        {
            try
            {
                $type = ColumnType::create_from_string($type, $this->nullable);
            }
            catch (\Error $e)
            {
                throw new Error("Can not create Column of type $type");
            }
        }
        else
        {
            $this->nullable = $type->nullable;
        }
        $this->type = $type;
        $this->name = trim($name);
    }

    public function is_primary_key(): bool
    {
        return is_a($this, "PHP_Library\Database\Table\Column\PrimaryKey");
    }

    public function __get($property): string|null
    {
        if ($property !== 'type')
        {
            return null;
        }
        switch (Database::get_type())
        {
            case 'FileDatabase':
                return $this->type->get_php_type();
            case 'SQLDatabase':
                return $this->type->get_sql_type();
            default:
                throw new Error("No Database Type");
        }
    }

    /**
     * Check if the column name and property type suggests that this represents the ID or a unique key of the object.
     *
     * @param string $singluar_object_name The name for one instance of the object.
     * @param string $column_name The nameof the column.
     * @param ColumnType $type The columntype.
     * @return bool True if the column might be an ID, false otherwise.
     */
    public static function is_maybe_primary_key(string $singluar_object_name, string $column_name, ColumnType $type): bool
    {
        if (! $type->is('integer'))
        {
            return false;
        }
        $lower_column_name = strtolower($column_name);
        $singluar_object_name = strtolower($singluar_object_name);
        return $lower_column_name === "id"  || str_ends_with($lower_column_name, $singluar_object_name . "_id");
    }

    public static function create_from_reflection_property(string $singluar_object_name, ReflectionProperty $property): static
    {
        $type = ColumnType::create_from_reflection_property($property);
        $column_name = $property->getName();
        if (static::is_maybe_primary_key($singluar_object_name, $column_name, $type))
        {
            return  new PrimaryAutoIncrementKey($column_name);
        }
        if (! $property->getType()->isBuiltin())
        {
            $proto_object = (new ReflectionClass(
                $property->getType()->getName()
            ))->newInstanceWithoutConstructor();
            if (!$proto_object instanceof DatabaseModel)
            {
                throw new Error("This is not an DatabaseModel");
            }
            if (! Database::table_exists($proto_object::get_table_name()))
            {
                $proto_object::create_table();
            }
            return new ForeignKey($proto_object::get_table_name());
        }
        return new Column($property->getName(), $type);
    }
}
