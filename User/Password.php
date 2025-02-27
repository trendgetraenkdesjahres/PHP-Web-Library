<?php

namespace PHP_Library\User;

use PHP_Library\Error\Error;

class Password
{
    #[\SensitiveParameter]
    protected string $content;

    protected static array $strength_validations = [];

    // throws errors with verification
    public function __construct(#[\SensitiveParameter] string $password)
    {
        static::test_strength($password);
        $this->content = $password;
    }

    public static function add_strength_validation(string $error_message, callable $strength_test): void
    {
        $strength_validations[] = ['error' => $error_message, 'test' => $strength_test];
    }

    public function get_hash()
    {
        return password_hash($this->content, PASSWORD_DEFAULT);
    }

    public function __toString()
    {
        return "SensitiveParameterValue";
    }

    protected static function test_strength(string $password): void
    {
        foreach (static::$strength_validations as $strength_validation) {
            if (!call_user_func($strength_validation['test'], $password)) {
                throw new Error($strength_validation['error']);
            }
        }
    }
}
