<?php

namespace  PHP_Library\Database\Table;

use PHP_Library\Database\FileDatabase;

class FileTable extends DataTable
{
    /**
     * secret row id column name
     * @var string
     */
    static public string $default_id_column_name = 'rowid';

    public function select_count(): int
    {
        return count(
            FileDatabase::$data[$this->name][array_key_first(FileDatabase::$data[$this->name])]
        );
    }
}
