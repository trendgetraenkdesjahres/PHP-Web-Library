<?php

namespace Library\DataStorage;

use Library\DataStorage\Traits\Database;

/**
 * DatabaseStorage is a class that handles database storage using PDO.
 */
class DatabaseStorage implements DataStorageInterface
{
    use Database;
}
