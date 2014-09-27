--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

$dt = new dtime();
var_dump($dt instanceof dtime);

$dt = new dtime('12-05-1959 12:00:10');
var_dump($dt->format('Y-m-d H:i:s'));

$dt = new dtime('12-05-1959 12:00:10', 'Europe/Ljubljana');
var_dump($dt->format('Y-m-d H:i:s'));

?>
--EXPECTF--
bool(true)
string(%d) "1959-05-12 12:00:10"
string(%d) "1959-05-12 12:00:10"
