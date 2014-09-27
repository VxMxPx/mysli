--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

$date = '2014-04-01 12:10:20';
date_default_timezone_set('UTC');

var_dump(dtime::f(dtime::timezone, $date));
var_dump(dtime::f(dtime::timestamp, $date));
var_dump(dtime::f(dtime::time, $date));
var_dump(dtime::f(dtime::day, $date));
var_dump(dtime::f(dtime::month, $date));
var_dump(dtime::f(dtime::year, $date));
var_dump(dtime::f(dtime::sort, $date));
var_dump(dtime::f('Ymd', $date));

var_dump(dtime::f('Ymd', new dtime($date)));
var_dump(dtime::f('Ymd', new \DateTime($date)));

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
string(%d) "20140401"
string(%d) "20140401"
