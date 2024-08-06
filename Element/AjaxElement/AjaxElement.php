<?php

namespace Element\AjaxElement;

use Element\Element;

class AjexElement extends Element
{
    protected function set_id(): static
    {
        if (!$this->node->hasAttribute('id')) {
            $this->set_attribute('id', bin2hex(random_bytes(4)));
        }
        return $this;
    }
}
