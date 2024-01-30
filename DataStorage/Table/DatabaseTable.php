<?php

namespace Library\DataStorage\Table;

use Library\DataStorage\Table\Traits\Database;

class DatabaseTable extends Table implements TableInterface
{
    use Database;
}
