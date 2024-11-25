<?php

namespace PHP_Library\Database\Table\Column;

/**
 * Class PrimaryAutoIncrementKey
 *
 * Represents a primary key column with auto-increment functionality.
 * Extends the PrimaryKey class to include the auto-increment property.
 */
class PrimaryAutoIncrementKey extends PrimaryKey
{
    /**
     * Indicates that this column is auto-incrementing.
     *
     * @var bool
     */
    public static bool $auto_increment = true;

    /**
     * Constructor for the PrimaryAutoIncrementKey class.
     *
     * @param string $name The name of the primary auto-increment column.
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'int');
    }
}
