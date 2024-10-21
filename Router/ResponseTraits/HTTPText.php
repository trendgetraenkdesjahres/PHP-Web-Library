<?php

namespace  PHP_Library\Router\ResponseTraits;

trait HTTPText
{
    public function articulate(): void
    {
        echo $this;
    }
}
