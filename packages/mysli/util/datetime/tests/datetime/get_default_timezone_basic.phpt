--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

date_default_timezone_set('Europe/Ljubljana');
var_dump(dtime::get_default_timezone())

?>
--EXPECTF--
string(%d) "Europe/Ljubljana"
