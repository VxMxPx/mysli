--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s', ['type' => 'float', 'default' => 'hello']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\input' with message 'Invalid default value for type `float`. Require float value.' %a
