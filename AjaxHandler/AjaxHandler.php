<?php

namespace AjaxHandler;

use AjaxHandler\DataValidator\DataValidator;

class AjaxHandler
{
    public DataValidator $validator;

    protected array $actions;

    final public function __construct(?array $post_data = null)
    {
        $this->validator = new DataValidator($post_data ? $post_data : $_POST);
    }

    public function add_action(callable $function, bool $only_on_success = true): static
    {
        array_push($this->actions, ['only_on_success' => $only_on_success, 'function' => $function]);
        return $this;
    }

    public function send_response(): static
    {
        if ($errors = $this->validator->get_errors()) {
            $this->send_bad_response($errors);
        }
        return $this->send_good_response();
    }

    private function send_good_response(): static
    {
        foreach ($this->actions as $action) {
            call_user_func($action['function']);
        }
        return $this;
    }

    private function send_bad_response($errors): static
    {
        http_response_code(400);
        foreach ($this->actions as $action) {
            if ($action['only_on_success']) {
                continue;
            }
            call_user_func($action['function']);
        }
        echo json_encode(['errors' => $errors]);
        return $this;
    }
}
