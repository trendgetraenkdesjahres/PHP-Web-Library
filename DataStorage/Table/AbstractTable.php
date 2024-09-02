<?php

namespace  PHP_Library\DataStorage\Table;


abstract class AbstractTable
{
    /**
     * Get the name of the table.
     *
     * @return string The name.
     */
    abstract  public function get_name(): string;

    /**
     * Get a row from the table by its ID.
     *
     * @param int $id The ID of the row.
     * @return array The row data.
     */
    abstract  public function get_row(int $id): array;

    /**
     * Get a cell from the table based on WHERE conditions. If there multiple matches, it will just return the first one.
     *
     * @param string $return_column The column to retrieve.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The cell value.
     */
    abstract public function get_cell_where(string $return_column, string ...$where_condition);

    /**
     * Get any rows from the table based on WHERE conditions.
     *
     * @param string ...$where_condition The WHERE conditions.
     * @return array An array of rows.
     */
    abstract public function get_any_row_where(?string ...$where_condition): array;

    /**
     * Get a related cell from another table by ID.
     *
     * @param int $id The ID to match in the related table.
     * @param AbstractTable $related_table The related table.
     * @param string $related_return_column The column to retrieve from the related table.
     * @return mixed The related cell value.
     */
    abstract public function get_related_cell(
        int $id,
        AbstractTable $related_table,
        string $related_return_column,
    ): mixed;

    /**
     * Get a related cell from another table based on WHERE conditions.
     *
     * @param AbstractTable $related_table The related table.
     * @param string $related_return_column The column to retrieve from the related table.
     * @param string ...$where_condition The WHERE conditions.
     * @return mixed The related cell value.
     */
    abstract public function get_related_cell_where(
        AbstractTable $related_table,
        string $related_return_column,
        string ...$where_condition,
    ): mixed;

    /**
     * Add a new row to the table.
     *
     * @param array $key_value_pairs An array of key-value pairs for the new row.
     * @return static The updated table instance.
     */
    abstract public function add_row(array $key_value_pairs): static;

    /**
     * Set the value of a cell in a row by ID.
     *
     * @param int $id The ID of the row.
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @return static The updated table instance.
     */
    abstract public function set_cell(int $id, string $column, mixed $value): static;

    /**
     * Set the value of a cell based on WHERE conditions.
     *
     * @param string $column The column name.
     * @param mixed $value The new value.
     * @param mixed $where_condition The WHERE condition.
     * @return static The updated table instance.
     */
    abstract public function set_cell_where(string $column, mixed $value, $where_condition): static;

    /**
     * Delete a row by its ID.
     *
     * @param int $id The ID of the row to delete.
     * @return static The updated table instance.
     */
    abstract  public function delete_row(int $id): static;

    /**
     * Delete rows based on WHERE conditions.
     *
     * @param mixed $where_condition The WHERE condition.
     * @return static The updated table instance.
     */
    abstract public function delete_row_where($where_condition): static;
    abstract public function __construct(string $name);
}
