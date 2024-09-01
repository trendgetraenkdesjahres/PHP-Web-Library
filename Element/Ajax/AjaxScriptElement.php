<?php

namespace PHP_Library\Element\Ajax;

use PHP_Library\Element\Element;

/**
 * - action after submit
 * - action after response (by code)
 */


class AjaxScriptElement
{
    private string $endpoint;

    protected array $code_for_response = [];

    public function __construct(protected string $object_id, protected string $subject_id, protected string $event, string $ajax_endpoint = "/ajax", protected string $method = 'post')
    {
        $this->endpoint = "window.location.origin + '{$ajax_endpoint}'";
        $this->code_for_response[200] = [
            "var object = document.getElementById('{$this->object_id}');",
            "object.innerText = JSON.parse(xhr.responseText);"
        ];
    }

    public function __toString()
    {
        $js_object_var =  "var subject = document.getElementById('{$this->subject_id}');";
        $js_object_event_listener = "subject.addEventListener('{$this->event}', function(event) {\n{$this->get_event_callback()}\n});";
        return new Element(
            'script',
            [],
            $js_object_var . "\n" . $js_object_event_listener
        );
    }

    protected function get_event_callback(): string
    {
        return implode("\n", [
            "event.preventDefault();",
            "var xhr = new XMLHttpRequest();",
            "xhr.open('{$this->method}', {$this->endpoint});",
            "xhr.setRequestHeader('Accept', 'application/json');",
            "xhr.onload = function() {\n{$this->get_onload_callback()}}",
            "xhr.send();"
        ]);
    }

    protected function get_onload_callback(): string
    {
        $string = '';
        foreach ($this->code_for_response as $response_code => $js_code_lines) {
            $if_statement = $string ? "else if (xhr.status === {$response_code})" : "if (xhr.status === {$response_code})";
            $string .= implode("\n", [
                $if_statement,
                "{\n{$this->get_js_code_for_response($response_code)}\n}"
            ]) . "\n";
        };
        return $string . "else {console.log(JSON.parse(xhr.responseText));}";
    }

    protected function get_js_code_for_response(int $code): string
    {
        if (!isset($this->code_for_response[$code])) {
            throw new \Error("No js-code for code '{$code}' is defined.");
        }
        return implode("\n", $this->code_for_response[$code]);
    }
}
