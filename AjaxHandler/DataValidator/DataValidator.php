<?php

namespace AjaxHandler\DataValidator;

class DataValidator
{
    protected array $errors;

    protected array $keys_to_check;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    function has_value(string|int ...$key): static
    {
        foreach ($key as $key) {
            if (empty($this->data[$key])) {
                array_push($this->errors, new Error($key, "Data for '{$key}' does not exist"));
            }
        }
        return $this;
    }

    function preg_match_value(string|int $key, string $pattern, string $error_message): static
    {
        if (!preg_match($pattern, $this->data[$key])) {
            array_push($this->errors, new Error($key, $error_message));
        }
        return $this;
    }

    function filter_var_value(string|int $key, int $filter, string $error_message): static
    {
        if (!filter_var($this->data[$key], $filter)) {
            array_push($this->errors, new Error($key, $error_message));
        }
        return $this;
    }

    /**
     * @return Error[]
     */
    public function get_errors(): array
    {
        return $this->errors;
    }
}
