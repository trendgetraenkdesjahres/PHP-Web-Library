<?php

namespace PHP_Library;

class ClassAutoloader
{
    /**
     * @var string $includes_dir The directory path where non-`PHP_Library` classes are located.
     */
    private static string $includes_dir;

    /**
     * @var bool $initated Indicates whether the autoloader has been initiated (singleton pattern).
     */
    protected static bool $initated = false;

    /**
     * @var string $php_web_lib_dir The base directory path for the `PHP_Library` namespace classes.
     */
    private static string $php_web_lib_dir;

    /**
     * Constructs a `ClassAutoloader` singleton-instance.
     *
     * This constructor initializes the class autoloader for the `PHP_Library` namespace and
     * also allows loading of non-`PHP_Library` classes if they are located in the specified includes directory.
     *
     * @param string $includes_dir The directory where class files are located, relative to the project root.
     */
    public function __construct(string $includes_dir = 'inc')
    {
        // force singleton
        if (!self::$initated) {
            $php_web_lib_dir = dirname(__FILE__);
            self::$initated = self::init($php_web_lib_dir, $includes_dir);
        }
    }

    /**
     * Initializes the autoloader.
     *
     * This method sets up the autoloader for both `PHP_Library` classes and non-`PHP_Library` classes
     * within the specified directories. It cleans up the directory paths and registers the autoloader function.
     *
     * @param string $framework_dir The base directory of the `PHP_Library` framework.
     * @param string $includes_dir  The directory where non-`PHP_Library` class files are located.
     * @return bool Returns true if the autoloader was successfully registered.
     */
    public static function init(string $framework_dir, string $includes_dir): bool
    {
        // clean paths
        self::$includes_dir = rtrim($includes_dir, '/');
        self::$php_web_lib_dir = rtrim($framework_dir, '/');

        // register method
        return spl_autoload_register([__CLASS__, 'autoloader_function']);
    }

    /**
     * The autoloader function for loading classes.
     *
     * This function attempts to load a class by determining whether it belongs to the `PHP_Library` namespace
     * or is a non-`PHP_Library` class in the includes directory. It then calls the appropriate method to
     * generate the file path and include the class file.
     *
     * @param string $full_class_name The fully-qualified class name including the namespace.
     * @return bool Returns true if the class file was successfully included; otherwise, false.
     */
    private static function autoloader_function($full_class_name): bool
    {
        if (!self::is_class_in_namespace($full_class_name)) {
            return self::include(
                class_file: self::get_generic_class_file($full_class_name),
                full_class_name: $full_class_name,
                throw_error: false
            );
        }
        return self::include(
            class_file: self::get_php_lib_class_file($full_class_name),
            full_class_name: $full_class_name
        );
    }

    /**
     * Generates the file path for a non-`PHP_Library` class.
     *
     * This method returns the file path for a class that is not within the `PHP_Library` namespace,
     * based on the specified directory.
     *
     * @param string $full_class_name The fully-qualified class name including the namespace.
     * @return string The file path for the class.
     */
    private static function get_generic_class_file($full_class_name): string
    {
        return self::$includes_dir . DIRECTORY_SEPARATOR . self::replace_class_seperator($full_class_name) . '.php';
    }

    /**
     * Generates the file path for a `PHP_Library` class.
     *
     * This method returns the file path for a class that belongs to the `PHP_Library` namespace,
     * based on the `PHP_Library` base directory.
     *
     * @param string $full_class_name The fully-qualified class name excluding the `PHP_Library` namespace.
     * @return string The file path for the class within the `PHP_Library` directory.
     */
    private static function get_php_lib_class_file($full_class_name): string
    {
        // remove namespace-base name, because the dirname of the library can differ.
        $full_class_name = substr($full_class_name, strlen(__NAMESPACE__ . "\\"));
        // add dirname of library
        return self::$php_web_lib_dir . DIRECTORY_SEPARATOR . self::replace_class_seperator($full_class_name) . '.php';
    }

    /**
     * Includes the class file.
     *
     * This method includes the specified class file if it exists. If the file is not found, it throws an error.
     *
     * @param string $class_file The file path of the class to include.
     * @param string $full_class_name The fully-qualified name of the class.
     * @param bool $throw_error If true, the method will throw an Error...
     * @return bool Returns true if the file was successfully included.
     * @throws \Error if the class file is not found.
     */
    private static function include(string $class_file, string $full_class_name, bool $throw_error = true): bool
    {
        if (! file_exists($class_file)) {
            if ($throw_error) {
                throw new \Error("Class '{$full_class_name}'-File '{$class_file}' not found.");
            }
            return false;
        }
        include $class_file;
        return true;
    }

    /**
     * Checks if a class belongs to the `PHP_Library` namespace.
     *
     * @param string $full_class_name The fully-qualified class name including the namespace.
     * @return string Returns an integer as a string if the class is in the `PHP_Library` namespace; otherwise, false.
     */
    public static function is_class_in_namespace($full_class_name): string
    {
        return is_int(strpos($full_class_name, __NAMESPACE__));
    }

    /**
     * Replaces the namespace separators with directory separators in a class name.
     *
     * This method converts the namespace separators (`\`) in a fully-qualified class name to
     * directory separators (`/` or `\` depending on the system).
     *
     * @param string $class_name The class name with namespace separators.
     * @return string The class name with directory separators.
     */
    private function replace_class_seperator(string $class_name): string
    {
        return str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
    }
}
