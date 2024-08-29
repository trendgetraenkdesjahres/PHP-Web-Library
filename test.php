<?php

include 'ClassAutoloader.php';

use PHP_Library\ClassAutoloader;
use PHP_Library\Element\Ajax\ElementInteraction;
use PHP_Library\Element\Element;
use PHP_Library\Types\ArrayType;
use PHP_Library\Types\StringType;

ClassAutoloader::init('.', '.');

$arr = new ArrayType('me', 45);
