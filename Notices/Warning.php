<?php

namespace Notices;

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

        $message = get_called_class() . ":\t[$function_name] $message in $function_file";
        trigger_error(
            message: $message,
            error_level: E_USER_WARNING
        );
    }
}
