<?php

namespace Notices;

use Types\StringType;

class Warning extends Notice
{
    /**
     * Warning class extends the Notice class and handles custom warnings.
     */

    /**
     * Constructor for the Warning class.
     *
     * @param string $message The warning message to be associated with this warning.
     */
    public function __construct(string $message)
    {
        parent::__construct($message); // Make sure to call the parent constructor
    }

    /**
     * Trigger a custom warning with a message and additional information.
     *
     * @param string $message The warning message to display.
     */
    public static function trigger(string $message)
    {
        $function_name = self::getFunctionName(trace_limit: 4);
        $function_file = self::getFunctionFile(trace_level: 3);

        if (class_exists('')) {
            $message = new StringType("Warning: $message");
            $message->append_line("\n$function_file")->box_around_string(2, $function_name);
            $message = "Warning:\t$function_name $message in $function_file\n";
        } else {
            $title = "Warning";
            $message = $message;
            $function_name = "[$function_name]";
            $function_file = $function_file;
            $message = "$title:\t$function_name $message in $function_file\n";
        }
        echo $message;
    }
}
