<?php

namespace PHP_Library\Superglobals;

use PHP_Library\DatabaseModel\DatabaseModel;
use PHP_Library\DatabaseModel\UserModel;
use PHP_Library\Error\Warning;

/**
 * Session is started by most of the methods. It must be started prior the first output. Use `Session::start()`, if the first other method is called within output.
 */
class Session extends DatabaseModel
{
    /** object in db id */
    public int $id;

    public string $session_id;
    public string $start_time;
    public string $ip_address;
    public string $user_agent;
    public ?int $user_id = null;

    private function __construct()
    {
        if (!static::is_active()) {
            throw new \Error("No Session active.");
        }
        $this->session_id = static::get_id();
        $this->start_time = static::get_field('_invoked_at');
        $this->user_id = (int) static::get_field('_user_id') ?? null;
        $this->ip_address = Server::get_remote_ip() ?? '';
        $this->user_agent = Server::get_http_user_agent() ?? '';
    }

    public static function get_current(): static
    {
        return new static();
    }

    /**
     * Starts the session if it's not already active.
     *
     * @param array $options Optional session configuration.
     * @return bool Returns true if session started successfully.
     */
    public static function start(array $options = []): bool
    {
        if (static::is_active()) {
            return false;
        }
        if (!session_start($options)) {
            return false;
        }
        static::set_field('_invoked_at', time());
        static::set_field('_user_id', UserModel::is_logged_in() ? UserModel::get_current()->user_id : '');
        return true;
    }

    /**
     * Regenerates session ID.
     *
     * @param bool $deleteOldSession If true, deletes old session.
     * @return bool
     */
    public static function regenerate(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Checks if a session is currently active.
     *
     * @return bool
     */
    public static function is_active(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Checks if a session key exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has_field(string $key): bool
    {
        static::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Retrieves  the session ID.
     *
     * @return string
     */
    public static function get_id(): string
    {
        static::start();
        return session_id();
    }

    /**
     * Retrieves a session value or the session ID.
     *
     * @param string|null $key If null, returns session ID.
     * @return mixed|null
     */
    public static function get_field(string $key): mixed
    {
        static::start();
        if (!static::has_field($key)) {
            Warning::trigger("Undefined Session Field '{$key}'");
            return null;
        }
        return $_SESSION[$key];
    }

    /**
     * Sets a session variable.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set_field(string $key, mixed $value): void
    {
        static::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Unsets a session variable or clears session data.
     *
     * @param string $key
     * @param string ...$keys Additional keys to unset.
     * @return bool
     */
    public static function unset_field(string $key, ?string ...$keys): bool
    {
        foreach (array_merge([$key], $keys) as $k) {
            unset($_SESSION[$k]);
        }
        return true;
    }

    /**
     * Destroys the session completely.
     *
     * @return bool
     */
    public static function destroy(): bool
    {
        if (! session_unset()) {
            return false;
        }
        if (! session_destroy()) {
            return false;
        }
        if (! static::request_session_deletion()) {
            return false;
        }
        return true;
    }

    protected static function request_session_deletion(): bool
    {
        $params = session_get_cookie_params();
        return setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
}
