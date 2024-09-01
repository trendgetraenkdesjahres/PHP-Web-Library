<?php

use PHP_Library\Autoloader;

include 'Autoloader.php';

// Start the autoloader by creating a new ClassAutoloader instance.
// This sets up autoloading for both PHP_Library and other classes found in the parent directory, guessing it's the includes directory with all the classes.
new Autoloader(dirname(__FILE__, 2));
