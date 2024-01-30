<?php

namespace Library\Model;

use Library\DataStorage\DataStorage;
use Library\DataStorage\DataStorageTableInterface;
use Library\DataStorage\TableColumn;
use Library\Notices\Warning;

abstract class Model
{
    private static bool $initialized = false;

    /**
     * Constructor for Models,
     * calls init_model(), which creates DB-Tables, like any other method that implements access_data()
     *
     * @return  void
     */
    public function __construct()
    {
        self::init_model();
    }

    /**
     * Get access to all the data of the class
     *
     * @return DataStorageTableInterface
     */
    private static function access_data(): DataStorageTableInterface
    {
        if (!self::$initialized) {
            self::init_model();
        }
        return DataStorage::get_table(get_called_class());
    }

    final public static function init_model()
    {
        $class = get_called_class();
        if (!DataStorage::table_exists($class)) {
            Warning::trigger("'" . $class . "' has no table yet. will create one");
            self::create_table($class);
        }
        self::$initialized = true;
    }

    private static function create_table($class)
    {
        $columns = [];
        foreach ((new \ReflectionClass($class))->getProperties() as $property_reflection) {
            if (!$property_reflection->getType()->isBuiltin()) {
                throw new \Error("Model does not support custom types yet.");
            }
            if ($property_reflection->getType()->getName() == 'array') {
                throw new \Error("Model does not support arrays yet.");
            }
            if ($property_reflection->isPrivate()) {
                continue;
            }
            array_push($columns, new TableColumn(
                name: $property_reflection->getName(),
                type: $property_reflection->getType()->getName(),
                length: null,
                nullable: $property_reflection->getType()->allowsNull()
            ));
        }
        DataStorage::create_table($class, ...$columns);
    }

    /**
     * Add in instance of this class
     *
     * @param array $property_value_pairs The data must represent the declared properties of the class.
     * @return DataStorageTableInterface
     */
    final static function add_instance(array $property_value_pairs): DataStorageTableInterface
    {
        return self::access_data()->add_row($property_value_pairs);
    }

    /**
     * Get's the value of a property from this class, where another value matches a property.
     * If there multiple matches, it will just return the first one
     *
     * @param string $property the property to return
     * @param string $property_value_pairs The conditions to check
     * @return mixed the value of the property
     */
    final static function get_value_where(string $property, string ...$property_value_pairs): mixed
    {
        return self::access_data()
            ->get_cell_where($property, ...$property_value_pairs);
    }

    final static function get_instance_where(string $property_value_pair): self
    {
        try {
            return self::array_to_instance(
                array: self::access_data()
                    ->get_any_row_where($property_value_pair)[0]
            );
        } catch (\Error $error) {
            throw new \Error("There is no '" . get_called_class() . "' where '$property_value_pair'");
        }
    }

    final static function get_instance(int $id): self
    {
        try {
            $array = self::access_data()
                ->get_row($id);
        } catch (\Error $error) {
            throw new \Error("There is no '" . get_called_class() . "' of id=$id");
        }
        return self::array_to_instance($array);
    }

    private static function array_to_instance(array $array): self
    {
        $reflection = new \ReflectionClass(get_called_class());
        $object = $reflection->newInstanceWithoutConstructor();
        foreach ($array as $property => $value) {
            if ($property === 'id') {
                continue;
            }
            if (!$reflection->hasProperty($property)) {
                throw new \Error("'" . get_called_class() . "' has no property named '$property'");
            }
            $property_reflection = $reflection->getProperty($property);
            $property_reflection->setAccessible(true);
            $property_reflection->setValue($object, $value);
        }
        return $object;
    }

    /**
     * Checks if an instance with a value of a property already exists
     *
     * @param string $property
     * @param string $value
     * @return bool
     */
    final static function has_instance_with_value(string $property, string $value): bool
    {
        if (self::access_data()->get_cell_where($property, "$property = '$value'")) {
            return true;
        }
        return false;
    }
}
