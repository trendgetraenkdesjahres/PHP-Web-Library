<?php

namespace PHP_Library\ObjectModel;

use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\Column;
use ReflectionClass;
use ReflectionProperty;

trait ObjectModelTrait
{
    abstract public static function get_singular_name(): string;

    abstract public static function get_plural_name(): string;

    public static function create_table(): bool
    {
        $table_name = static::get_plural_name();
        if (Database::table_exists($table_name)) {
            return false;
        }

        $table_columns = static::get_table_columns();

        Database::create_table(
            'users',
            new Column('username'),
            new Column('password'),
            new Column('link_page_path', nullable: true),
            new Column('link_page_title', nullable: true),
            new Column('session_id', 'int', nullable: true),
        );
        $users = Database::get_table('users');
        $users->insert_row('admin', password_hash('lala1234', PASSWORD_DEFAULT));
    }

    /**
     *
     * @return Column[]
     */
    protected static function get_table_columns(): array
    {
        $table_columns = [];
        $class = new ReflectionClass(get_called_class());
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }
        }
    }
}
