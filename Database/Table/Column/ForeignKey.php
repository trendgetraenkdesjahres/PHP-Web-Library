<?php

namespace PHP_Library\Database\Table\Column;

use Error;
use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\ColumnType\VarChar;
use PHP_Library\Database\Table\DataTable;

class ForeignKey extends Column
{

    protected DataTable $referenced_table;

    public function __construct(DataTable|string $references_table, null|Column|string $references_column = null)
    {
        if (is_string($references_table))
        {
            if (! Database::table_exists($references_table))
            {
                throw new Error("Table '{$references_table}' does not exist.");
            }
            $references_table = Database::get_table($references_table);
        }
        $this->referenced_table = $references_table;

        if (is_null($references_column))
        {
            $references_column = $this->referenced_table->get_primary_key();
        }
        if (!is_string($references_column))
        {
            $references_column = $references_column->name;
        }
        $type = new VarChar();
        $type->nullable = false;
        parent::__construct(
            $references_column,
            $type
        );
    }
}
