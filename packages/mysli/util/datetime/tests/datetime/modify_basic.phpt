--TEST--
--FILE--
<?php
use mysli\util\datetime\datetime as dtime;

$dt = new dtime('2014-04-01 12:10:20', 'UTC');

var_dump($dt->modify('+1 day')->format(dtime::day));

?>
--EXPECTF--
string(%d) "02"
