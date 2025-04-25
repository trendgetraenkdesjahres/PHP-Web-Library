<?php

namespace PHP_Library\Types\StringExtensions;

use PHP_Library\Types\StringType;

use PHP_Library\Error\Error;

class PasswordString extends StringType
{
    private string $password;

    protected static array $strength_validations = [];


    public static function add_strength_validation(string $error_message, callable $strength_test): void
    {
        $strength_validations[] = ['error' => $error_message, 'test' => $strength_test];
    }

    public static function test_strength(string $password): void
    {
        foreach (static::$strength_validations as $strength_validation)
        {
            if (!call_user_func($strength_validation['test'], $password))
            {
                throw new Error($strength_validation['error']);
            }
        }
    }

    public function get_hash()
    {
        return password_hash($this->value, PASSWORD_DEFAULT);
    }

    protected function to_string(): string
    {
        return "SensitiveParameterValue";
    }
}
