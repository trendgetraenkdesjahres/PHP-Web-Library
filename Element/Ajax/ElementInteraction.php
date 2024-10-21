<?php

namespace PHP_Library\Element\Ajax;

use PHP_Library\Element\Element;
use PHP_Library\Router\Endpoint;

class ElementInteraction
{
    protected string $id;
    protected string $object_id;
    protected string $subject_id;
    protected string $trigger;
    protected string $endpoint;

    public function __construct(callable $new_content, Element $object, Element $subject, string $trigger_action)
    {
        $this->id = $this->get_unique_string();
        $this->trigger = $trigger_action;
        $this
            ->set_element_ids($object, $subject)
            ->set_endpoint($new_content);
    }

    public function __toString()
    {
        return (string) new AjaxScriptElement($this->object_id, $this->subject_id, $this->trigger, $this->endpoint, 'get');
    }

    protected function set_endpoint(callable $callback, string $prefix = '/ajax/elements/', string $method = 'get'): static
    {
        $path = $prefix . $this->id;
        $endpoint = new Endpoint(
            path: $path,
            http_method: $method,
            response_class: 'JSONResponse'
        );
        $this->endpoint = $path;
        $endpoint->add_callback($callback);
        return $this;
    }

    private function set_element_ids(Element &$object, Element &$subject): static
    {
        if (!$object->get_attribute('id')) {
            $object->set_attribute('id', bin2hex(random_bytes(4)));
        }
        $this->object_id = $object->get_attribute('id');
        if (!$subject->get_attribute('id')) {
            $subject->set_attribute('id', bin2hex(random_bytes(4)));
        }
        $this->subject_id = $subject->get_attribute('id');
        return $this;
    }

    private function get_unique_string(): string
    {
        $called_from = debug_backtrace(limit: 2)[1];
        return hash('md4', $called_from['file'] . $called_from['line']);
    }
}
