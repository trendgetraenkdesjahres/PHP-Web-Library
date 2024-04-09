<?php

namespace  PHP_Library\DataStorage;

use  PHP_Library\Settings\Settings;

require_once 'Tables/Table.php';
require_once 'Database.php';
require_once 'File.php';



/**
 * DataStorageInterface defines the contract for DataStorage classes.
 */
interface DataStorageInterface
{
    public static function initalize(): bool;
    public static function create_table(string $table, TableColumn ...$columns): bool;
    public static function get_queried_data(): mixed;
    public static function get_table(string $table_name): DataStorageTableInterface;
    public static function table_exists(string $table_name): bool;
}

/**
 * DataStorage is a factory class for creating DataStorageTable instances.
 */
class DataStorage
{
    /**
     * Get a table instance by name.
     *
     * @param string $name The name of the table.
     * @return DataStorageTableInterface The table instance.
     * @throws \Error If no suitable configuration for Filebased or DBbased setting is found.
     */
    public static function get_table(string $name): DataStorageTableInterface
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
            throw new \Error("No Setting for 'datastorage/database_name' or 'datastorage/file_name' found.");
        }
    }

    /**
     * Create a table instance based on the specified name and columns.
     *
     * @param string $name The name of the table.
     * @param TableColumn ...$columns Variable-length list of TableColumn objects representing table columns.
     * @return DataStorageTableInterface The created table instance.
     * @throws \Error If no suitable configuration for 'datastorage/database_name' or 'datastorage/file_name' is found.
     */
    public static function create_table(string $name, TableColumn ...$columns): void
    {
        if (Settings::get('datastorage/database_name')) {
            if (DatabaseStorage::initalize()) {
                DatabaseStorage::create_table($name, ...$columns);
            }
        }
        if (Settings::get('datastorage/file_name')) {
            if (FileStorage::initalize()) {
                FileStorage::create_table($name, ...$columns);
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
        }
        if (Settings::get('datastorage/file_name')) {
            if (FileStorage::initalize()) {
                return FileStorage::table_exists($name);
            }
        } else {
            throw new \Error("No Setting for 'datastorage/database_name' or 'datastorage/file_name' found.");
        }
    }
}