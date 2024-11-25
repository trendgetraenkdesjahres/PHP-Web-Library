<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\FileDatabase;

/**
 * Class FileTable
 *
 * Represents a table stored in a file-based database. Provides functionality for querying and interacting
 * with file-stored data, extending the generic DataTable class.
 *
 * Dependencies:
 * - PHP_Library\Database\FileDatabase: For accessing file-based database storage.
 */
class FileTable extends DataTable
{
    /**
     * Default column name for the (hidden) row identifier.
     *
     * @var string
     */
    static public string $default_id_column_name = 'rowid';

    /**
     * Counts the number of rows in the table.
     *
     * @return int The number of rows in the table.
     */
    public function select_count(): int
    {
        return count(
            FileDatabase::$data[$this->name][array_key_first(FileDatabase::$data[$this->name])]
        );
    }
}
