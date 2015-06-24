--TEST--
--FILE--
<?php
use mysli\util\datetime\datetime as dtime;

var_dump(dtime::now());

$dt = new dtime();
var_dump($dt::now());

?>
--EXPECTF--
int(%d)
int(%d)
