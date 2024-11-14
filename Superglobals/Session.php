<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Superglobals\Error\SessionError;

/**
 * Session is started by most of the methods. It must be started prior the first output. Use `Session::start()`, if the first other method is called within output.
 */
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
        static::initialize();
        if (!isset($_SESSION[$key])) {
            return null;
        }
        return $_SESSION[$key];
    }

    public static function set(string $key, mixed $value): void
    {
        static::initialize();
        $_SESSION[$key] = $value;
    }

    public static function unset(?string $key = null, ?string ...$keys): bool
    {
        static::initialize();
        if (is_null($key) && !empty($keys)) {
            throw new SessionError("When unsetting the whole session, just `Session::unset(null)`.");
        }
        if (is_null($key)) {
            return session_unset();
        }
        $keys = array_merge([$key], $keys);
        foreach ($keys as $keys) {
            unset($_SESSION[$keys]);
        }
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

    protected static function initialize(): bool
    {
        if (is_null(static::$id)) {
            return static::start();
        }
        return true;
    }
}
