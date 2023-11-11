<?php

use DataStorage\FileStorage;
use Notices\Warning;

class FileQueryInterface implements QueryInterface

{
    public array $condition;

    public function select(string ...$return_columns): array
    {
        foreach ($this->condition as $where_condition) {
            switch ($where_condition['operator']) {
                case '=':
                    if ($where_condition['operator'] == 'id') {
                        $ids = [$where_condition['value']];
                    } else {
                        $ids = array_keys(
                            array: FileStorage::get_data()[$table][$where_condition['column']],
                            filter_value: $where_condition['value'],
                        );
                    }
                    foreach ($ids as $i => $id) {
                        foreach ($return_columns as $column) {
                            $query_result[$i][$column] = FileStorage::get_data()[$table][$column][$id];
                        }
                        if (count($query_result[$i]) > 1) {
                            $query_result[$i]['id'] = $id;
                        }
                    }
                    break;

                default:
                    Warning::trigger("SELECT: Operant '{$where_condition['operator']}': not implmented.");
                    break;
            }
        }
    }

    public function insert(string $table, array $key_value_pairs)
    {
        // Implement logic to insert a new row into the file-based storage.
        // Example: append data to the file.
    }

    public function update(string $column, $value)
    {
        foreach ($this->condition as $where_condition) {
            if (!$where_condition['value'] != 'id') {
                Warning::trigger("UPDATE: Operant '{$where_condition['value']}': not implmented.");
            }
            if (!$where_condition['column'] != 'id') {
                Warning::trigger("UPDATE: Column '{$where_condition['operator']}': has to be 'id'");
            }
        }
    }

    public function delete(string $table, int $id)
    {
        // Implement logic to delete a row from the file-based storage.
        // Example: locate and remove the row by ID.
    }


    private function set_where_condition(string ...$where_conditions): FileQueryInterface
    {
        $sql_operators = [
            '=',
            '>',
            '>=',
            '<',
            '<=',
            '<>',
            'BETWEEN',
            'LIKE',
            'IN',
            'NOT IN',
        ];

        foreach ($where_conditions as $i => $where_condition) {
            if (strpos($where_condition, " AND ")) {
                throw new \Error("Multiple WHERE clause are not implemented yet");
            }
            try {
                foreach ($sql_operators as $operator) {
                    if (is_int(strpos(
                        haystack: $where_condition,
                        needle: $operator
                    ))) {
                        $array = explode($operator, $where_condition);
                        $this->condition[$i] = [
                            'column' => trim($array[0]),
                            'operator' => $operator,
                            'value' => trim(trim($array[1]), "'\"")
                        ];
                        break;
                    }
                }
            } catch (\Throwable $e) {
                Warning::trigger("WHERE clause is broken: '$where_condition'");
            }
        }
        return $this;
    }
}
