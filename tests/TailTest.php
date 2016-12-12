<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Tail.php';

use Tail\Tail;

$t = new Tail('/Users/tairy/Documents/working/webhook/lib/test.txt');
$t->tailf();