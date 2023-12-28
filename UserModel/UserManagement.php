<?php

namespace UserManagement;

use Debug\Debug;
use Model\Model;

class User extends Model
{
    public string $password;
    public string $email;
    public ?string $name;
    public function __construct(string $email, string $name)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Error("'$email' is not an email.");
        }
        $this->email = $email;
        $this->name = $name;
    }

    public function register(string $password): User
    {
        if (self::has_instance_with_value('email', $this->email)) {
            throw new \Error("User '$this->email' already registered.");
        }
        if (!self::validate_password($password)) {
            throw new \Error("Password is not safe.");
        }
        self::add_instance([
            'name' => $this->name,
            'email' => $this->email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        return $this;
    }

    public function authenticate(string $password): User
    {
        if (!password_verify(
            password: $password,
            hash: self::get_value_where('password', "username = '{$this->email}'")
        )) {
            throw new \Error("Could not login '{$this->email}'.");
        }
        return $this;
    }

    public static function get(int $id): User
    {
        return self::get_instance($id);
    }

    private static function validate_password(string $password)
    {
        if (
            strlen($password) >= 8 &&
            preg_match('@[0-9]@', $password) &&
            preg_match('@[A-Z]@', $password) &&
            preg_match('@[a-z]@', $password) &&
            preg_match('@[^\w]@', $password)
        ) {
            return true;
        }
        return false;
    }
}
