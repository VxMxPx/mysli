--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\datetime as dtime;

$dt = new dtime('2014-04-01 12:10:20', 'UTC');

var_dump($dt->format(dtime::timezone));
var_dump($dt->format(dtime::timestamp));
var_dump($dt->format(dtime::time));
var_dump($dt->format(dtime::day));
var_dump($dt->format(dtime::month));
var_dump($dt->format(dtime::year));
var_dump($dt->format(dtime::sort));
var_dump($dt->format('Ymd'));

?>
--EXPECTF--
string(%d) "UTC"
int(%d)
string(%d) "12:10:20"
string(%d) "01"
string(%d) "04"
string(%d) "2014"
string(%d) "20140401121020"
string(%d) "20140401"
