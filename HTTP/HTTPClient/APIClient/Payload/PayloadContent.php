<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload;

abstract class  PayloadContent
{
    abstract public function merge(PayloadContent $new_content, bool $override = true): static;
}
