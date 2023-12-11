<?php

namespace Model;

use DataStorage\DataStorage;
use DataStorage\DataStorageTableInterface;

class Model
{
    protected int $id;
    private DataStorageTableInterface $table;

    public function __construct()
    {
        if (get_class($this) === 'Model') {
            throw new \Error('don`t call me directly!!');
        }
        if (!DataStorage::table_exists(get_class($this))) {
            die(get_class($this) . ' does not existsss');
            // TODO create table with params
        }
        $this->table = DataStorage::get_table(get_class($this));
    }

    private function get_properties()
    {
    }
}
