<?php

namespace PHP_Library\Database\Table\Column;

/**
 * Class PrimaryKey
 *
 * Represents a primary key column in a database table. Extends the base Column class
 * to provide specific functionality for primary keys.
 */
class PrimaryKey extends Column
{
    /**
     * Indicates that this column is a primary key.
     *
     * @var bool
     */
    public static bool $is_primary_key = true;

    /**
     * Constructor for the PrimaryKey class.
     *
     * @param string $name The name of the primary key column.
     * @param string $type The data type of the primary key.
     * @param int|null $length The maximum length of the primary key (optional).
     */
    public function __construct(string $name, string $type, ?int $length = null)
    {
        parent::__construct($name, $type, $length);
    }
}
