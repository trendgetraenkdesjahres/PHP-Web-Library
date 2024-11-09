<?php

namespace  PHP_Library\DataStorage;

use PHP_Library\DataStorage\Table\AbstractTable;
use PHP_Library\DataStorage\Tables\DataStorageTableInterface;
use PHP_Library\DataStorage\Table\Column;
use PHP_Library\Error\Error;
use  PHP_Library\Settings\Settings;



/**
 * DataStorage is a factory class for creating DataStorageTable instances.
 */
abstract class DataStorage
{
    /**
     * Get a table instance by name.
     *
     * @param string $name The name of the table.
     * @return AbstractTable The table instance.
     * @throws \Error If no suitable configuration for Filebased or DBbased setting is found.
     */
    public static function get_table(string $name): AbstractTable
    {
        if (Settings::get('datastorage/database_name')) {
            if (DatabaseStorage::initalize()) {
                return DatabaseStorage::get_table($name);
            }
        }
        if (Settings::get('datastorage/file_name')) {
            FileStorage::initalize();
            return FileStorage::get_table($name);
        } else {
            throw new Error("No Setting for 'datastorage/database_name' or 'datastorage/file_name' found.");
        }
    }

    /**
     * Create a table instance based on the specified name and columns.
     *
     * @param string $name The name of the table.
     * @param Column ...$columns Variable-length list of TableColumn objects representing table columns.
     * @return DataStorageTableInterface The created table instance.
     * @throws \Error If no suitable configuration for 'datastorage/database_name' or 'datastorage/file_name' is found.
     */
    public static function create_table(string $name, Column ...$columns): bool
    {
        if (Settings::get('datastorage/database_name')) {
            if (DatabaseStorage::initalize()) {
                return DatabaseStorage::create_table($name, ...$columns);
            }
        }
        if (Settings::get('datastorage/file_name')) {
            if (FileStorage::initalize()) {
                return FileStorage::create_table($name, ...$columns);
            }
        } else {
            throw new \Error("No Setting for 'datastorage/database_name' or 'datastorage/file_name' found.");
        }
    }

    public static function table_exists(string $name): bool
    {
        if (Settings::get('datastorage/database_name')) {
            if (DatabaseStorage::initalize()) {
                return DatabaseStorage::table_exists($name);
            }
            throw new Error("Unable to initalize DatabaseStorage");
        }
        if (Settings::get('datastorage/file_name')) {
            if (FileStorage::initalize()) {
                return FileStorage::table_exists($name);
            }
            throw new Error("Unable to initalize FileStorage");
        } else {
            throw new Error("No Setting for 'datastorage/database_name' or 'datastorage/file_name' found.");
        }
    }
}
