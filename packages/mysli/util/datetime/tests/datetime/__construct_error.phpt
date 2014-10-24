--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;

$dt = new dtime(['error']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Invalid $datetime object type.' in %a
