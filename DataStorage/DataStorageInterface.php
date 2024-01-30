<?php

namespace Library\DataStorage;

use Library\DataStorage\Table\Table;
use Library\DataStorage\Table\TableColumn;

/**
 * DataStorageInterface defines the contract for DataStorage classes.
 */
interface DataStorageInterface
{
    public static function initalize(): bool;

    /**
     * Create a table instance based on the specified name and columns.
     *
     * @param string $name The name of the table.
     * @param TableColumn ...$columns Variable-length list of TableColumn objects representing table columns.
     * @return Table The created table instance.
     * @throws \Error If no suitable configuration for 'datastorage/database_name' or 'datastorage/file_name' is found.
     */
    public static function create_table(string $table, TableColumn ...$columns): bool;


    public static function get_queried_data(): mixed;

    /**
     * Get a table instance by name.
     *
     * @param string $name The name of the table.
     * @return Table The table instance.
     * @throws \Error If no suitable configuration for Filebased or DBbased setting is found.
     */
    public static function get_table(string $table_name): Table;
    public static function table_exists(string $table_name): bool;
}
