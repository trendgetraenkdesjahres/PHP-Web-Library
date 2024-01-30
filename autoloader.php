<?php
spl_autoload_register(function ($class_name): bool {
    $library_name = 'Library';
    if (!str_starts_with($class_name, "$library_name\\")) {
        return false;
    }
    $path = __DIR__ . DIRECTORY_SEPARATOR . str_replace(
        search: '\\',
        replace: DIRECTORY_SEPARATOR,
        subject: substr($class_name, strlen("$library_name\\")) . '.php'
    );
    var_dump($path);
    if (file_exists($path)) {
        require $path;
        return true;
    }
    echo "$library_name-Autoloader: Could not include '$path' for '$class_name'";
    return false;
});
