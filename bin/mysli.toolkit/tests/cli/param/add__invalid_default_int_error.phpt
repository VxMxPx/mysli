--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s', ['type' => 'int', 'default' => 'hello']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\input' with message 'Invalid default value for type `int`. Require integer value.' %a
