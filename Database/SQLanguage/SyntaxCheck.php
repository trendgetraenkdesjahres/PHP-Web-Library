<?php

namespace PHP_Library\Database\SQLanguage;

use PHP_Library\Database\SQLanguage\Error\SQLanguageError;

/**
 * Class SyntaxCheck
 *
 * Provides static methods to validate SQL syntax elements such as field names,
 * table names, data types, and safe values.
 * Throws exceptions for invalid syntax if $throws_errors is enabled.
 */
class SyntaxCheck
{
    /**
     * Determines whether invalid syntax triggers exceptions.
     *
     * @var bool
     */
    public static bool $throws_errors = true;

    /**
     * Private constructor to prevent instantiation of this utility class.
     */
    private function __construct() {}

    /**
     * Validates whether a value is safe to use in SQL queries.
     *
     * @param string|null $value The value to validate.
     *
     * @return bool True if the value is safe; otherwise, throws an exception or returns false.
     */
    public static function is_safe_value(string|null $value)
    {
        $value = is_null($value) ? 'NULL' : $value;
        if (preg_match('/[\'";]/', $value)) {
            return self::throw_exception("`$value` is not a safe value.");
        }
        return true;
    }


    /**
     * Validates whether a string is a valid SQL field name.
     *
     * @param string $field_name The field name to validate.
     *
     * @return bool True if valid; otherwise, throws an exception or returns false.
     */
    public static function is_field_name(string $field_name): bool
    {
        if (20 < strlen($field_name)) {
            return self::throw_exception("`$field_name` is an invalid name: too long for a field name.");
        }
        $first_character = $field_name[0];
        if (!ctype_alpha($first_character)) {
            return self::throw_exception("`$field_name` is an invalid name: first character must be [a-Z].");
        }
        foreach (str_split($field_name) as $character) {
            if ((!ctype_alpha($character)) && $character != '_') {
                return self::throw_exception("`$field_name` is an invalid name: character must be [a-Z] or '_'.");
            }
        }
        return true;
    }

    /**
     * Validates whether a string is a valid SQL table name.
     *
     * @param string $table_name The table name to validate.
     *
     * @return bool True if valid; otherwise, throws an exception or returns false.
     */
    public static function is_table_name(string $table_name): bool
    {
        if (20 < strlen($table_name)) {
            return self::throw_exception("`$table_name` is an invalid name: too long for a table name.");
        }
        $first_character = $table_name[0];
        if (!ctype_lower($first_character)) {
            return self::throw_exception("`$table_name` is an invalid name: first character must be [a-z].");
        }
        foreach (str_split($table_name) as $character) {
            if ((!ctype_lower($character)) && $character != '_') {
                return self::throw_exception("`$table_name` is an invalid name: character must be [a-z] or '_'.");
            }
        }
        return true;
    }

    /**
     * Validates whether a string is a valid SQL data type.
     *
     * @param string $type The SQL data type to validate.
     *
     * @return bool True if valid; otherwise, throws an exception or returns false.
     */
    public static function is_data_type(string $type): bool
    {

        if (!preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|text|tinytext|datetime)\s*(unsigned)?$/',
            subject: $type
        )) {
            return self::throw_exception("`$type` is an invalid type.");
        }
        return true;
    }

    /**
     * Checks whether a given SQL data type is indexable.
     *
     * @param string $type The SQL data type to validate.
     *
     * @return bool True if indexable; otherwise, throws an exception or returns false.
     */
    public static function is_indexable_data_type(string $type): bool
    {
        $matches = [];
        if (!preg_match(
            pattern: '/^(bigint\((\d+)\)|varchar\((\d+)\)|int\((\d+)\)|datetime)\s*(unsigned)?$/',
            subject: $type,
            matches: $matches
        )) {
            return self::throw_exception("`$type` is not an indexable type.");
        }
        foreach ($matches as $match) {
            if (!$match) continue;
            if ($match === $type) continue;
            if ($match > 20) {
                return self::throw_exception("`$type` is not an indexable type. $match is larger than 20.");
            }
        }
        return true;
    }

    /**
     * Throws an exception or returns false based on $throws_errors.
     *
     * @param string $message The error message to throw or log.
     *
     * @return bool Always false if exceptions are not enabled.
     * @throws SQLanguageError If $throws_errors is true.
     */
    private static function throw_exception(string $message): bool
    {
        if (static::$throws_errors) {
            throw new SQLanguageError($message);
        }
        return false;
    }
}
