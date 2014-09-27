--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\util\datetime as dtime;

date_default_timezone_set('UTC');
var_dump(date_default_timezone_get());
dtime::set_default_timezone('Europe/Ljubljana');
var_dump(date_default_timezone_get());

?>
--EXPECTF--
string(%d) "UTC"
string(%d) "Europe/Ljubljana"
