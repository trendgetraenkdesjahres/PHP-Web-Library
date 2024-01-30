<?php

namespace Library\DataStorage;

use Library\DataStorage\Table\Table;
use Library\DataStorage\Table\TableColumn;
use Library\Settings\Settings;

/**
 * DataStorage is a factory class for creating DataStorageTable instances.
 */
abstract class DataStorage implements DataStorageInterface
{
    public static function get_table(string $name): Table
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

    // TODO HALLOOO!!! hier ist factory u nicht implementierung
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
