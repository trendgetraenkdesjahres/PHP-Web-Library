<?php

namespace PHP_Library\User;

use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\Column\PrimaryAutoIncrementKey;
use PHP_Library\Database\Table\DataTable;
use ReflectionClass;

abstract class User
{
    public readonly int $id;
    public string $name;
    protected static DataTable $table;

    final public function create(string $name, string $password)
    {
        static::initiate();
        $password = new Password($password);
        static::$table->insert_row($name, $password->get_hash());
        $this->attributes = $attributes;
    }

    final protected function __construct(string $name, string $password) {}

    private static function initiate(): bool
    {
        if (isset(static::$table)) {
            return true;
        }
        if(!Database::table_exists('users')) {
            static::create_user_table();
        }
        try {
            static::$table = Database::get_table('users');
        } catch (\Throwable $t) {
            return false;
        }
        return true;
    }

    protected static function create_user_table(): void {
        $columns = [];
        foreach((new ReflectionClass(User::class))->getProperties(IS_PUBLIC) as $property) {
            $column_name = strtolower($property->getName());
            $column_type = $property->getType();
            $columns[] = new Column()
        }
    }
}
