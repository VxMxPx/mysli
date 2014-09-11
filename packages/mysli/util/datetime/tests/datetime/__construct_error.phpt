--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\datetime as dtime;

$dt = new dtime(['error']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'Invalid $datetime object type.' in %a
