<?php

include 'Autoload.php';

use AjaxHandler\AjaxScript;
use Element\Element;
use PHP_Library\Autoload;

$autoload = new Autoload();
$autoload->init();

$button = new Element('button', ['type' => 'submit', 'id' => '1234'], 'Submit');
$test = new Element('form', ['id' => '123', 'method' => 'post', 'action' => ''], 'moin', $button);

$script = new AjaxScript('123', 'submit');

$script->add_interactive_element($button);

echo $test . $script;
