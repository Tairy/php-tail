<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Tail.php';

use Tail\Tail;

$t = new Tail('/Users/tairy/Documents/working/SF_Ops/test.txt');
//$t->tailf();
//echo $t->tail(4);
while (true) {
//    echo $t->readByPos($lastpos);
//    echo $lastpos;
}
