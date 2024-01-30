<?php

namespace Library\DataStorage;

use Library\DataStorage\Traits\File;


/**
 * FileStorage is a class that handles data storage using files.
 */
class FileStorage implements DataStorageInterface
{
    use File;
}
