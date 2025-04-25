<?php

namespace PHP_Library\DatabaseModel;

use PHP_Library\CLIConsole\CLIConsoleTrait;
use PHP_Library\Error\Error;
use PHP_Library\Superglobals\Session;
use PHP_Library\Types\StringExtensions\PasswordString;

class UserModel extends DatabaseModel
{
    public string $name;

    public int $user_id = 0;

    public ?Session $session = null;

    public string $password_hash;

    protected ?bool $is_registered = null;

    protected PasswordString $password;

    private static self $current_user;


    use CLIConsoleTrait;

    public function __construct(string $name, string $password)
    {
        $this->name = $name;
        $this->password = new PasswordString($password);
        $this->password_hash = $this->password->get_hash();
    }

    public static function get_current(): static
    {
        if (isset(static::$current_user))
        {
            return static::$current_user;
        }
        if (!Session::is_active())
        {
            throw new Error("No current user.");
        }
        try
        {
            return static::$current_user = static::select_instance_where_equals('session_id', Session::get_id());
        }
        catch (\Throwable $e)
        {
            throw new Error("No active user session in Database for session_id '" . Session::get_id() . "'");
        }
    }

    public static function is_logged_in(): bool
    {
        try
        {
            return static::get_current()->has_valid_session();
        }
        catch (\Throwable $e)
        {
            return false;
        }
    }

    public static function get_by_name(string $user_name): static
    {
        try
        {
            return static::select_instance_where_equals('name', $user_name);
        }
        catch (\Throwable $e)
        {
            throw new Error("Unknown user '{$user_name}'");
        }
    }

    public function check_password(string $password): static
    {
        if (password_verify($password, $this->password_hash))
        {
            return $this;
        }
        throw new Error("Wrong password");
    }

    public function end_session(): static
    {
        Session::unset_field('user_name');
        $this->session = null;
        $this->update_instance();
        return $this;
    }

    public function start_session(string $password): static
    {
        $this->check_password($password);
        return $this->start_session_raw();
    }

    protected function start_session_raw(): static
    {
        Session::set_field('user_name', $this->name);
        $this->session = Session::get_current();
        if ($this->update_instance())
        {
        }
        return $this;
    }

    public function has_valid_session(): bool
    {
        if (!$this->is_persistent())
        {
            throw new Error("User has no persistence with Database");
        }
        if ($this->session->session_id === Session::get_id())
        {
            return true;
        }
        return false;
    }

    public function register(): bool
    {
        if ($this->is_registered)
        {
            throw new Error("A user with the name '{$this->name}' is already registered.");
        }
        $this->password_hash = $this->password->get_hash();
        if (!$this->has_given_name())
        {
            return $this->is_registered = $this->insert_instance();
        }
        throw new Error("A user with the name '{$this->name}' is already registered.");
    }

    protected function has_given_name(): bool
    {
        $result = static::$table->select("name")->where_equals("name", $this->name)->get(false);
        if (count($result) === 0)
        {
            return false;
        }
        foreach ($result as $user)
        {
            if ($user["name"] === $this->name)
            {
                return true;
            }
        }
        return false;
    }
}
