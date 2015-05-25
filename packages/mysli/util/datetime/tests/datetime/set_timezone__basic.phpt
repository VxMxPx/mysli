--TEST--
--FILE--
<?php
use mysli\util\datetime\datetime as dtime;

$dt = new dtime('2014-08-10 12:00:10', 'UTC');
var_dump($dt->format('Y-m-d H:i:s'));

$dt->set_timezone('Europe/Ljubljana');
var_dump($dt->format('Y-m-d H:i:s'));

?>
--EXPECTF--
string(%d) "2014-08-10 12:00:10"
string(%d) "2014-08-10 14:00:10"
