<?php

namespace  PHP_Library\Router\Response\Traits;

trait HTTPText
{
    public function articulate(): void
    {
        echo $this;
    }
}
