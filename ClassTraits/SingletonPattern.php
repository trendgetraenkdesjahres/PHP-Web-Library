<?php

namespace PHP_Library\ClassTraits;

/**
 * Trait implementing the Singleton pattern.
 * Provides methods to manage a single instance of a class.
 * Dependent on the class that uses it to define the constructor.
 */
trait SingletonPattern
{
    /**
     * Holds the single instance of the class.
     * @var self
     */
    protected static self $singleton_instance;

    /**
     * Prevents direct instantiation of the class.
     * Abstract method that must be implemented by the using class.
     */
    abstract private function __construct();

    /**
     * Initializes the singleton instance if not already set.
     * @return void
     */
    final protected static function init_singleton(): void
    {
        if (!isset(self::$singleton_instance))
        {
            self::$singleton_instance = new self();
        }
    }

    /**
     * Gets the singleton instance.
     * @return static
     */
    final protected static function get_singleton(): static
    {
        static::init_singleton();
        return self::$singleton_instance;
    }

    /**
     * Prevents unserialization of the singleton instance.
     * @throws \Exception if an attempt to unserialize is made.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
