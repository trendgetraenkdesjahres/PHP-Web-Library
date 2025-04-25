<?php

namespace PHP_Library\DatabaseModel;

use PHP_Library\ClassTraits\MirrorTrait;
use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\Column\ColumnType;
use PHP_Library\Database\Table\DataTable;
use PHP_Library\DatabaseModel\Error\Error;

/**
 * Trait DatabaseModelTrait
 *
 * This trait provides basic CRUD (Create, Read, Update, Delete) operations for objects stored in a database.
 * - `insert_instance()`: Inserts a new record into the database.
 * - `update_instance()`: Updates an existing record.
 * - `delete_instance()`: Deletes an object from the database.
 * - `select_instance()`: Retrieves a record by ID.
 * - `select_instance_where_like()`: Retrieves a record by matching a column.
 */
trait DatabaseModelTrait
{
    use MirrorTrait;

    /**
     * @var bool $persistence Indicates whether the instance is persisted in the database.
     */
    protected bool $persistence = false;

    /**
     * @var DataTable $table Reference to the database table associated with the model.
     */
    protected static DataTable $table;

    /**
     * @var string[] $propertie_hashes
     * An array with hashes for each property, initiated when instance is getting recreated from Database
     * or when instance updates it's Data in the Database.
     */
    protected static array $propertie_hashes;

    /**
     * @var string $primary_key The name of the primary key propery.
     */
    protected static string $primary_key;

    /**
     * Gets the singular name of the model.
     *
     * @return string Singular name of the model.
     */
    abstract public static function get_singular_name(): string;

    /**
     * Gets the plural name of the model.
     *
     * @return string Plural name of the model.
     */
    abstract public static function get_plural_name(): string;

    final public static function get_primary_key(): string
    {
        if (!isset(static::$primary_key))
        {
            $properties = static::get_public_properties();
            foreach ($properties as $property)
            {
                if (Column::is_maybe_primary_key(
                    static::get_singular_name(),
                    $property->getName(),
                    ColumnType::create_from_reflection_property($property)
                ))
                {
                    return static::$primary_key = $property->getName();
                }
            }
            throw new Error("No property name and type suggests beeing a primary key");
        }
        return static::$primary_key;
    }

    /**
     * Inserts a new instance into the database.
     *
     * @throws Error If a required property is missing.
     * @return bool True if insertion was successful, false otherwise.
     */
    final public function insert_instance(): bool
    {
        $values = [];
        foreach (static::get_public_properties() as $property)
        {
            $property_name = $property->getName();

            // the primary key is AUTO INCREMENT, so has to get dropped from the statement.
            if ($property_name === static::get_primary_key())
            {
                continue;
            }
            if (!isset($this->$property_name))
            {
                if ($property->getType()->allowsNull())
                {
                    $values[$property_name] = 0;
                    continue;
                }
                throw new Error(get_called_class() . "'->{$property_name}' can not be null/undef.");
            }
            $values[$property_name] = $this->$property_name;
        }
        if (static::$table->insert_row(...$values))
        {
            return $this->persistence = true;
        }
        return false;
    }

    /**
     * Updates an existing instance in the database.
     *
     * @return bool True if the update was successful, false otherwise.
     */
    final public function update_instance(): bool
    {
        $values = [];
        foreach ($this->get_transient_properties() as $property)
        {
            $values[$property] = $this->$property;
        }
        $primary_key = static::get_primary_key();
        if ($values === static::$table->update_row($this->$primary_key, $values))
        {
            return $this->persistence = true;
        }
        return $this->persistence = false;
    }

    /**
     * Deletes the instance from the database.
     *
     * @return bool True if deletion was successful, false otherwise.
     */
    final public function delete_instance(): bool
    {
        $this->persistence = false;
        return static::$table->delete_row($this->id);
    }

    /**
     * Retrieves an instance by its ID.
     *
     * @param int $id The ID of the record.
     * @return static The retrieved instance.
     */
    final public static function select_instance(int $id): static
    {
        $data = static::select_instance_data($id);
        if (!$data)
        {
            throw new Error("No data for " . static::get_plural_name() . "[{$id}] found.");
        }
        return static::get_proto_object()
            ->initialize_object($data);
    }

    /**
     * Retrieves an instance based on a column value match.
     *
     * @param string $property The column name.
     * @param string|int $value The value to search for.
     * @return static The retrieved instance.
     */
    final public static function select_instance_where_equals(string $property, string|int $value): static
    {
        $data = static::select_instance_data_where_equals($property, $value);
        if (!$data)
        {
            throw new Error("No data for " . static::get_plural_name() . " found where {$property}='{$value}'");
        }
        return static::get_proto_object()
            ->initialize_object($data);
    }

    /**
     * Creates the database table for the model.
     *
     * @return bool True if table creation was successful, false otherwise.
     */
    final public static function create_table(): bool
    {
        if (!isset(static::$table))
        {
            static::$table = Database::get_table(static::get_table_name());
        }
        if (Database::table_exists(static::get_table_name()))
        {
            return false;
        }
        $table_columns = static::get_table_columns();
        return Database::create_table(
            static::get_table_name(),
            ...$table_columns
        );
    }

    /**
     * Checks if the instance is persisted in the database.
     *
     * @return bool True if persisted, false otherwise.
     */
    final public function is_persistent(): bool
    {
        if ($this->persistence)
        {
            $this->persistence = ! $this->is_transient();
        }
        return $this->persistence;
    }

    /**
     * Checks if the instance is transient (not stored in the database).
     *
     * @return bool True if transient, false otherwise.
     */
    final public function is_transient(): bool
    {
        return (bool) $this->get_transient_properties();
    }

    protected function get_transient_properties(): array
    {
        $outdated_properties = [];
        foreach (static::get_public_properties() as $property_reflection)
        {
            $property = $property_reflection->getName();
            if (!isset($this->$property))
            {
                continue;
            }
            if (static::$propertie_hashes[$property] === static::hash_value($this->$property))
            {
                continue;
            }
            $outdated_properties[] = $property;
        }
        if ($outdated_properties)
        {
            $this->persistence = false;
        }
        return $outdated_properties;
    }

    /**
     * Initializes the object with given values.
     *
     * @param array $public_values Property values.
     * @return static Initialized object.
     */
    private function initialize_object(array $public_values): static
    {
        foreach ($public_values as $property => $value)
        {
            if (! property_exists($this, $value))
            {
                throw new Error(static::get_class_name() . "->{$property} does is not defined.");
            }
            static::$propertie_hashes[$property] = static::hash_value($value);
            $this->$property = $value;
        }
        $this->persistence = true;
        return $this;
    }

    /**
     * Table name.
     *
     * @return string The name of the data table of this object.
     */
    public static function get_table_name(): string
    {
        return lcfirst(static::get_plural_name());
    }


    /**
     * Retrieves the columns for the database table.
     *
     * @return Column[] Array of Column objects representing the table structure.
     */
    private static function get_table_columns(): array
    {
        $primary_keys = [];
        $table_columns = [];
        foreach (static::get_public_properties() as $property)
        {
            $column = Column::create_from_reflection_property(static::get_singular_name(), $property);
            if ($column->is_primary_key())
            {
                array_push($primary_keys, $column->name);
            }
            array_push($table_columns, $column);
        }
        if (count($primary_keys) == 0)
        {
            throw new Error("Class " . static::get_class_name() . " needs to have a primary key: public int \$id");
        }
        if (count($primary_keys) > 1)
        {
            throw new Error("Found more than one primary key candidates: '" . \implode("', '", $primary_keys) . "'.");
        }
        static::$primary_key = $primary_keys[0];
        return $table_columns;
    }

    /**
     * Selects data from the database by the instance ID.
     *
     * @param int $id The ID of the record.
     * @return array The row data associated with the given ID.
     */
    private static function select_instance_data(int $id): array
    {
        return Database::get_table(static::get_table_name())->get_row($id);
    }

    /**
     * Selects data based on a column value match.
     *
     * @param string $property The column name.
     * @param string|int $value The value to search for.
     * @return array|false The matched row data or false if not found.
     */
    private static function select_instance_data_where_equals(string $property, string|int $value): array|false
    {
        if (Database::get_table(static::get_table_name())->select()->where_equals($property, $value)->execute())
        {
            return Database::get_query_result()[0];
        }
        return false;
    }

    private static function hash_value(mixed $value): string
    {
        return hash("crc32b", serialize($value));
    }
}
