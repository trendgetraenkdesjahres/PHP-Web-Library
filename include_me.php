<?php

use PHP_Library\Autoloader;

include 'Autoloader.php';

// Start the autoloader by creating a new ClassAutoloader instance.
// This sets up autoloading for both PHP_Library and other classes found in the parent directory, guessing it's the includes directory with all the classes.
new Autoloader(dirname(__FILE__, 2));

function vd(mixed $var = null, ...$vars)
{
    $vars = [$var] + $vars;
    ob_start();
    \var_dump(...$vars); // Use the fully qualified global function
    $output = ob_get_clean();
    $last = debug_backtrace()[0];
    echo "<h2>{$last['file']}:{$last['line']}</h2><pre>{$output}</pre>";
}
function dd(mixed $var = null, ...$vars)
{
    vd($var, ...$vars);
    die();
}
