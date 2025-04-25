<?php

namespace PHP_Library\CLIConsole\Command;

use PHP_Library\CLIConsole\Error\Error;

class Command
{
    public string $namespace;
    public string $function_name;
    public string $class_or_trait;

    public array $parameters = [];

    public function __construct(public string $input)
    {
        $input = explode(" ", $input);
        $this->set_command(array_shift($input));
        if (! empty($input))
        {
            $this->set_parameter($input);
        }
    }

    public function set_command(string $first_token): static
    {
        $command = array_reverse(explode("::", $first_token));
        $this->function_name = $command[0];
        $this->namespace = isset($command[1]) && $command[1] ? $command[1] : "\\";
        return $this;
    }

    public function set_parameter(array $parameter_tokens): static
    {
        $named_parameters = true;
        foreach ($parameter_tokens as $parameter_token)
        {
            $parameter = array_reverse(explode("=", $parameter_token, 2));
            // positional
            if (count($parameter) == 1)
            {
                $this->parameters[] = $parameter[0];
                $named_parameters = false;
                continue;
            }
            // named
            if (!$named_parameters)
            {
                throw new Error("Positional argument after named argument.");
            }
            $this->parameters[$parameter[1]] = $parameter[0];
        }
        return $this;
    }

    public function execute(): mixed
    {
        if ($this->is_method())
        {
            return call_user_func_array([$this->namespace, $this->function_name], $this->parameters);
        }
        if ($this->is_function())
        {
            return call_user_func_array($this->function_name, $this->parameters);
        }
        else
        {
            set_error_handler(function ($errno, $errstr, $errfile, $errline)
            {
                throw new Error($errstr, $errno);
            });
            try
            {
                $tokens = explode(" ", $this->input);
                $vars = '';
                foreach ($tokens as $token)
                {
                    if (preg_match('/^\$[a-zA-Z]\w*/', $token))
                    {
                        $vars .= "$token,";
                    }
                }
                if ($vars)
                {
                    $vars = substr($vars, 0, -1);
                    $vars = "global $vars;";
                }
                $this->input = str_ends_with($this->input, ";") ? $this->input : $this->input . ";";
                $result = eval($vars . "return " . $this->input);
            }
            catch (\Throwable $e)
            {
                throw new Error($e->getMessage());
            }
            restore_error_handler();
            return $result;
        }
    }

    public function is_method(): bool
    {
        return $this->namespace != '\\' && method_exists($this->namespace, $this->function_name);
    }

    public function is_function(): bool
    {
        return $this->namespace == '\\' && function_exists($this->function_name);
    }

    public function get_reflection(): \ReflectionMethod|\ReflectionFunction|null
    {
        if ($this->is_method())
        {
            return new \ReflectionMethod((string) $this);
        }
        else if ($this->is_function())
        {
            return new \ReflectionFunction((string) $this);
        }
        return null;
    }

    public function __toString()
    {
        if ($this->namespace !== '\\')
        {
            return "{$this->namespace}::{$this->function_name}";
        }
        return "{$this->namespace}{$this->function_name}";
    }
}
