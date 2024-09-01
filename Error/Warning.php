<?php

namespace PHP_Library\Error;

class Warning extends Notice
{
    protected static $user_errno = E_USER_WARNING;
    protected static $errno = E_WARNING;
}
