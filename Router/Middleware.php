<?php

namespace  PHP_Library\Router;

use PHP_Library\Superglobals\Session;

class Middleware
{
    protected array $filter;

    protected array $required_permissions;

    protected string $user;

    public function __construct(?callable $filter = null)
    {
        if ($filter) {
            $this->filter = [$filter];
        }
    }

    /**
     * if a user has privilegeske
     */
    public function authorize(string ...$required_permission): static
    {
        foreach ($required_permission as $required_permission) {
            $this->required_permissions[] = $required_permission;
        }
        return $this;
    }

    /**
     * if a (certain) user is logged in
     */
    public function authenticate(string $username = null): bool
    {
        if (! Session::has_field('username')) {
            return false;
        }
        if ($username) {
            return $username === Session::get_field('username');
        }
        return true;
    }
}
