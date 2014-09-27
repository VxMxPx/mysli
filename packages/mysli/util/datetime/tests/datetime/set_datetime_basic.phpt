--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

$dt = new dtime('2014-08-10 12:00:10');
var_dump($dt->format('Y-m-d H:i:s'));

$dt->set_datetime('2010-04-10 10:00:00');
var_dump($dt->format('Y-m-d H:i:s'));

$dt->set_datetime(strtotime('2009-04-10 10:00:00'));
var_dump($dt->format('Y-m-d H:i:s'));

$dt->set_datetime(new dtime('2008-04-10 10:00:00'));
var_dump($dt->format('Y-m-d H:i:s'));

$dt->set_datetime(new DateTime('2007-04-10 10:00:00'));
var_dump($dt->format('Y-m-d H:i:s'));

?>
--EXPECTF--
string(%d) "2014-08-10 12:00:10"
string(%d) "2010-04-10 10:00:00"
string(%d) "2009-04-10 10:00:00"
string(%d) "2008-04-10 10:00:00"
string(%d) "2007-04-10 10:00:00"
