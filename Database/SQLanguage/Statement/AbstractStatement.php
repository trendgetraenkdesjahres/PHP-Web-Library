<?php

namespace PHP_Library\Database\SQLanguage\Statement;

use PHP_Library\Database\Database;
use PHP_Library\Database\SQLanguage\SyntaxCheck;

/**
 * Class Select
 *
 * Implementations are a SELECT/INSERT/ ... classes representing sql statements.
 */
abstract class AbstractStatement
{
    /** @var string The name of the table */
    public readonly string $table;

    /** @var string The specified columns as string */
    public string $columns_string = '';

    /** @var array The specified columns */
    public array $columns = [];

    private bool $has_result = false;

    abstract public function __toString(): string;

    /**
     * Executes this Statement on current DB.
     * To access data, call `Database::get_query_result()`
     *
     * @return boolean Success
     */
    public function execute(): bool
    {
        var_dump($this);
        return Database::query($this);
    }

    /**
     * Sets the table for the SELECT statement.
     *
     * @param string $table The name of the table. or an instance of AbstractTable
     * @return Select Instance of the current Select for method chaining.
     */
    protected function set_table(string $table): static
    {
        SyntaxCheck::is_table_name($table);
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the columns to be selected in the statement.
     *
     * @param array $columns An array of column names
     * @return AbstractStatement Instance of the current statement for method chaining.
     */
    protected function set_columns(array $columns): static
    {
        foreach ($columns as $column) {
            SyntaxCheck::is_field_name($column);
            $this->columns[] = $column;
            $this->columns_string .= "{$column}, ";
        }
        $this->columns_string = rtrim($this->columns_string, ', ');
        return $this;
    }
}
