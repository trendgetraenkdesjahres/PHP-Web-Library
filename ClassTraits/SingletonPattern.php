<?php

namespace PHP_Library\ClassTraits;

trait SingletonPattern
{
    protected static self $singleton_instance;

    abstract private function __construct();

    final protected static function init_singleton(): void
    {
        if (!isset(self::$singleton_instance)) {
            self::$singleton_instance = new self();
        }
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
