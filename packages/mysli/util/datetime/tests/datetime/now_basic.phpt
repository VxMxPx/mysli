--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

var_dump(dtime::now());

$dt = new dtime();
var_dump($dt::now());

?>
--EXPECTF--
int(%d)
int(%d)
