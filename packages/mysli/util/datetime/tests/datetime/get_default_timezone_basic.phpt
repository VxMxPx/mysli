--TEST--
--FILE--
<?php
use mysli\util\datetime\datetime as dtime;

date_default_timezone_set('Europe/Ljubljana');
var_dump(dtime::get_default_timezone())

?>
--EXPECTF--
string(%d) "Europe/Ljubljana"
