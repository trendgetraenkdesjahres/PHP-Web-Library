<?php

interface QueryInterface
{
    /**
     * Execute a SELECT query and return the result for file-based data storage.
     *
     * @param string $table The name of the table to query.
     * @param string $return_column The column to return.
     * @param string $where_condition The WHERE condition for filtering data.
     * @return mixed The query result.
     */
    public function select(string $table, string $return_column, string $where_condition);

    /**
     * Execute an INSERT query to add a row to the file-based data storage.
     *
     * @param string $table The name of the table to insert into.
     * @param array $key_value_pairs An associative array of key-value pairs to insert.
     */
    public function insert(string $table, array $key_value_pairs);

    /**
     * Execute an UPDATE query to set a cell's value in the file-based data storage.
     *
     * @param string $table The name of the table to update.
     * @param int $id The ID of the row to update.
     * @param string $column The name of the column to update.
     * @param mixed $value The new value to set.
     */
    public function update(string $table, int $id, string $column, $value);

    /**
     * Execute a DELETE query to remove a row from the file-based data storage.
     *
     * @param string $table The name of the table to delete from.
     * @param int $id The ID of the row to delete.
     */
    public function delete(string $table, int $id);
}


class Query
{
    public static function build(
        string $table,
        ?string $column = null,
        string $command = 'get',
        string $where_condition,
        mixed $value = null
    ): QueryInterface {
        
    }
}
