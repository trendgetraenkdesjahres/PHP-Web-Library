<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Superglobals\Error\SessionError;

class Session
{
    public static string $name;
    public static ?string $id = null;

    public static function start(?string $name = null): bool
    {
        if ($name) {
            session_name($name);
        }
        if (!session_start()) {
            return false;
        }
        static::$name = session_name();
        static::$id = session_id();
        return true;
    }

    public static function get(string $key): mixed
    {
        if (is_null(static::$id)) {
            throw new SessionError("Session not started.");
        }
        if (!isset($_SESSION[$key])) {
            return null;
        }
        return $_SESSION[$key];
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_null(static::$id)) {
            throw new SessionError("Session not started.");
        }
        $_SESSION[$key] = $value;
    }

    public static function unset(?string $key = null): bool
    {
        if (is_null(static::$id)) {
            throw new SessionError("Session not started.");
        }
        if (is_null($key)) {
            return session_unset();
        }
        if (!isset($_SESSION[$key])) {
            throw new SessionError("Undefined Session key '{$key}'.");
        }
        unset($_SESSION[$key]);
        return true;
    }

    public static function destroy(): bool
    {
        if (is_null(static::$name)) {
            throw new SessionError("Session not started.");
        }
        if (!session_destroy()) {
            return false;
        }
        $params = session_get_cookie_params();
        return setcookie(
            static::$name,
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
}
