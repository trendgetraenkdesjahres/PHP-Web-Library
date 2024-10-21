<?php

use PHP_Library\Router\Endpoint;

include 'include_me.php';

$index = Endpoint::new_text_file_endpoint('/', 'text.html');
$redirect = Endpoint::new_redirect_endpoint('/redirect', $index);
