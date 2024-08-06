<?php

namespace AjaxHandler\DataValidator;

class Error
{
    public readonly string $id;

    public readonly string $message;

    final public function __construct(string $id, string $message)
    {
        $this->id = $id;
        $this->message = $message;
    }
}
