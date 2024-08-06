<?php

namespace AjaxHandler;

use Element\Element;

/**
 * - action after submit
 * - action after response (by code)
 */


class AjaxScript
{
    private array $code_on_event = [];

    private array $code_on_received = [];

    private string $wrapper_id;

    private ?Element $interactive_element;

    private string $event;

    private string $method;

    private string $endpoint;

    public function __construct(string $wrapper_id, string $event, string $ajax_endpoint = 'window.location.origin + "/ajax"', string $method = 'POST')
    {
        $this->wrapper_id = $wrapper_id;
        $this->event = $event;
        $this->method = strtoupper($method);

        if (!is_int(strpos($ajax_endpoint, '"')) || !is_int(strpos($ajax_endpoint, "'"))) {
            $this->endpoint = "'{$ajax_endpoint}'";
        } else {
            $this->endpoint = $ajax_endpoint;
        }
    }

    public function add_interactive_element(Element $element): static
    {
        if (!$element->get_attribute('id')) {
            $element->set_attribute('id', bin2hex(random_bytes(4)));
        }
        $this->interactive_element = $element;
        return $this;
    }

    public function add_code_on_event(string $js_code): static
    {
        array_push($this->code_on_event, trim($js_code));
        return $this;
    }

    public function add_code_on_reponse_received(string $js_code, int $response_code = 200): static
    {
        if (!isset($this->code_on_received[$response_code])) {
            $this->code_on_received[$response_code] = [];
        }
        array_push($this->code_on_received[$response_code], $js_code);
        return $this;
    }

    public function __toString()
    {
        $string =
            "var wrapper = document.getElementById('{$this->wrapper_id}');" .
            "wrapper.addEventListener('{$this->event}', function(ev) {" .
            "ev.preventDefault();" .
            "var xhr = new XMLHttpRequest();";
        if (isset($this->interactive_element)) {
            $string .= "var el = document.getElementById('{$this->interactive_element->get_attribute('id')}');";
        }
        $string .=
            "xhr.open('{$this->method}', {$this->endpoint});" .
            "xhr.onload = function() {";
        if (isset($this->code_on_received)) {
            foreach ($this->code_on_received as $response_code => $js_codes) {
                $string .= "if (xhr.status === {$response_code}) {";
                foreach ($js_codes as $js_code) {
                    $string .= trim(trim($js_code), ';') . ';';
                }
            }
        } else {
            $string .= 'console.log(JSON.parse(xhr.responseText));';
        }
        $string .= "}; xhr.send();});";
        return "<script>{$string}</script>";
    }
}
