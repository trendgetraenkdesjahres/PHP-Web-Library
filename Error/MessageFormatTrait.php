<?php

namespace PHP_Library\Error;

trait MessageFormatTrait
{
    private static string $fallback_emoji = "🫤";
    public static bool $use_emoji_style = true;
    public static function get_emoji(int|Error|Notice|\Error $errno): string
    {
        if ($errno instanceof Error || $errno instanceof Notice) {
            return isset($errno::$emoji) ? $errno::$emoji : '⚡';
        }
        if ($errno instanceof \Error) {
            return '⚠️';
        }
        switch ($errno) {
            case E_ERROR:
                return "💥"; // Critical error
            case E_WARNING:
                return "⚠️"; // Warning
            case E_PARSE:
                return "🛑"; // Parse error (syntax error)
            case E_NOTICE:
                return "ℹ️"; // Notice (info message)
            case E_CORE_ERROR:
                return "🔥"; // Core error (serious issue)
            case E_CORE_WARNING:
                return "⚠️"; // Core warning
            case E_COMPILE_ERROR:
                return "💻❌"; // Compile-time error
            case E_COMPILE_WARNING:
                return "💻⚠️"; // Compile-time warning
            case E_USER_ERROR:
                return "🚨"; // User-defined error
            case E_USER_WARNING:
                return "⚠️"; // User-defined warning
            case E_USER_NOTICE:
                return "👤ℹ️"; // User-defined notice
            case E_RECOVERABLE_ERROR:
                return "🩹"; // Recoverable error
            default:
                return "❓"; // Unknown error
        }
    }
    public function __toString(): string
    {
        return static::format_message($this->message);
    }

    protected static function format_message($message, ?int $code = null, ?string $method = null, ?string $file = null, ?int $line = null, ?string $messsege_class = null)
    {
        $messsege_class = is_null($messsege_class) ? (new \ReflectionClass(get_called_class()))->getShortName() : $messsege_class;
        if (static::$use_emoji_style) {
            return static::format_emoji_message($message, $code, $method, $file, $line);
        }
        $code = is_int($code) ? "({$code})" : '';
        $method = $method ?  " [{$method}]" : "";
        return "{$messsege_class}{$code}{$method}: {$message}" . ($file && $line ? " in {$file}:{$line}" : "") . "\n";
    }

    protected static function format_emoji_message($message, ?int $code = null, ?string $method = null, ?string $file = null, ?int $line = null): string
    {
        $reflection = new \ReflectionClass(get_called_class());
        if (! $reflection->hasMethod('get_emoji')) {
            $emoji = static::$fallback_emoji;
        } else {
            $dummy = $reflection->newInstanceWithoutConstructor();
            $emoji = $dummy::get_emoji($dummy);
        }
        $linebreak = strlen($message) > 80 ? PHP_EOL : " ";
         return $emoji . ($file && $line ? " $method in {$file}:{$line}" : "$method"). $linebreak.($code ? "{$code}: " : ""). "{$message}" . "\n";
    }
}
