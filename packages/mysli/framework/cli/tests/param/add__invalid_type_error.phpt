--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s', ['type' => 'wrong!']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Invalid type: `wrong!`. Acceptable types: str, float, int, bool' %a