<?php

namespace PHP_Library\Debug;

/**
 * Class Context
 *
 * Represents a snapshot of debug context from the backtrace, such as file, line, class, and method info. 
 */
class Context
{
    /**
     * The maximum number of stack frames to return in a backtrace.
     *
     * @var int
     */
    protected static int $backtrace_limit = 16;

    protected static int $file_offset = 1;

    /**
     * The filename where the context was captured.
     *
     * @var string|null
     */
    public readonly ?string $file;

    /**
     * The line number in the file where the context was captured.
     *
     * @var string|null
     */
    public readonly ?string $line;

    /**
     * The short class name where the context was captured.
     *
     * @var string|null
     */
    public readonly ?string $class;

    /**
     * The namespace of the class where the context was captured.
     *
     * @var string|null
     */
    public readonly ?string $namespace;

    /**
     * The method or function name where the context was captured.
     *
     * @var string|null
     */
    public readonly ?string $method;

    /**
     * Whether the method was called statically (::) or not (->).
     *
     * @var bool|null
     */
    public readonly ?bool $static_method;

    /**
     * The arguments passed to the method or function.
     *
     * @var array|null
     */
    public readonly ?array $arguments;

    /**
     * The arguments passed to the method or function.
     *
     * @var array|null
     */
    private readonly ?string $code;

    /**
     * Context constructor.
     *
     * Extracts and assigns relevant context details from the backtrace.
     *
     * @param bool $ignore_exception_objects Whether to skip exception-related calls in the trace.
     */
    public function __construct(bool $ignore_exception_objects = true, ?array $trace = null)
    {
        $context_array = static::get_context_array($ignore_exception_objects, $trace);
        $this->file = $context_array['file'] ?? null;
        $this->line = $context_array['line'] ?? null;

        if (isset($context_array['class'])) {
            $class = explode("\\", $context_array['class']);
            $this->class = array_pop($class);
            $this->namespace = implode("\\", $class) ?? null;
        } else {
            $this->class = null;
            $this->namespace = null;
        }

        $this->method = $context_array['method'] ?? $context_array['function'] ?? null;
        $this->arguments = $context_array['args'] ?? null;

        if (isset($context_array['type'])) {
            $this->static_method = ($context_array['type'] === '::');
        } else {
            $this->static_method = null;
        }
    }

    /**
     * Get the filename from the context.
     *
     * @return string|null
     */
    public function get_file(bool $relative = true): ?string
    {
        if ($relative) {
            return substr($this->file, strlen(getcwd()) + 1);
        }
        return $this->file;
    }

    /**
     * Get the line number from the context.
     *
     * @return string|null
     */
    public function get_line(): ?string
    {
        return $this->line;
    }

    /**
     * Get the method or function name from the context.
     *
     * @return string|null
     */
    public function get_method(bool $include_class_name = true): ?string
    {
        if ($include_class_name && $this->class) {
            return $this->class . ($this->static_method ? "::" : "->") . $this->method . "()";
        }
        return $this->method . "()";
    }

    public function get_class(bool $short_name = false): ?string
    {
        if (!$this->class) {
            return null;
        }
        if ($short_name) {
            return $this->class;
        }
        return "$this->namespace\\$this->class";
    }

    public function get_code(): ?string
    {
        if (!isset($this->code)) {
            $this->code = static::create_code_snippet($this->file, $this->line);
        }
        return $this->code;
    }

    /**
     * Retrieves the most relevant call context from the debug backtrace.
     *
     * Skips over internal error and exception handling frames, depending on the ignore flag.
     *
     * @param bool $ignore_exception_objects Whether to skip exception and internal debug classes.
     * @return array The associative array of a backtrace frame.
     */
    protected static function get_context_array($ignore_exception_objects, ?array $trace = null): array
    {
        $trace = is_null($trace) ? debug_backtrace(2, limit: static::$backtrace_limit) : $trace;
        foreach ($trace as $depth => $frame) {
            if (
                $ignore_exception_objects
                && isset($frame['function'])
                && $frame['function'] === "trigger_error"
            ) {
                continue;
            }

            if (!isset($frame['class'])) {
                return static::apply_file_offset_frame($trace, $depth);
            }

            if (is_a($frame['class'], static::class, true)) {
                continue;
            }

            if (
                $ignore_exception_objects
                && (
                    is_a($frame['class'], \Error::class, true)
                    || is_a($frame['class'], \Exception::class, true)
                    || is_a($frame['class'], \PHP_Library\Error\Notice::class, true)
                )
            ) {

                continue;
            }
            return static::apply_file_offset_frame($trace, $depth);
        }

        return [];
    }

    protected static function apply_file_offset_frame(array $trace, int $trace_depth): array
    {
        $frame = $trace[$trace_depth];
        if (! static::$file_offset) {
            return $frame;
        }
        $file_trace_depth = $trace_depth + static::$file_offset;
        if (isset($frame['file']) && isset($trace[$file_trace_depth]['file'])) {
            $frame['file'] = $trace[$file_trace_depth]['file'];
        }
        if (isset($frame['line']) && isset($trace[$file_trace_depth]['line'])) {
            $frame['line'] = $trace[$file_trace_depth]['line'];
        }
        return $frame;
    }

    private static function create_code_snippet(string $file, int $line): ?string
    {
        $lines = explode(PHP_EOL, file_get_contents($file));
        $total = count($lines);

        $start = $line - 1;
        $end = $start;

        // Go upward to find the opening {
        $braces = 0;
        for ($i = $start; $i >= 0; $i--) {
            $braces += substr_count($lines[$i], '{');
            $braces -= substr_count($lines[$i], '}');

            if ($braces > 0) {
                $start = $i;
                break;
            }
        }

        // Reset and go downward to find the matching }
        $braces = 0;
        for ($i = $start; $i < $total; $i++) {
            $braces += substr_count($lines[$i], '{');
            $braces -= substr_count($lines[$i], '}');

            if ($braces === 0) {
                $end = $i;
                break;
            }
        }

        // Slice the code context out the whole file
        $code_section = array_slice($lines, $start, $end - $start + 1);

        // find left padding
        $left_padding = null;
        array_walk($code_section, function ($value) use (&$left_padding) {
            $indent = strspn($value, " ");
            if ($left_padding === null || $indent < $left_padding) {
                $left_padding = $indent;
            }
        });
        $left_padding = is_null($left_padding) ? 0 : $left_padding;

        // add line numbers and remove left padding
        $code_section = array_map(function ($value, $key) use ($start, $left_padding) {
            $line_number = $start + $key + 1;
            return "$line_number: " . substr($value, $left_padding);
        }, $code_section, array_keys($code_section));

        return implode(PHP_EOL, $code_section) ?? null;
    }
}
