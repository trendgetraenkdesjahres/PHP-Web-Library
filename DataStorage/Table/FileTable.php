<?php

namespace Library\DataStorage\Table;

use Library\DataStorage\Table\Traits\File;

/**
 * File is a concrete implementation of DataStorageTable for file-based tables.
 */
class FileTable extends Table implements TableInterface
{
    use File;
}
