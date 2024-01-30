<?php

namespace Library\DataStorage\Table;

abstract class Table
{
    public function __construct(public string $name)
    {
    }

    public function get_name(): string
    {
        return $this->name;
    }
}
