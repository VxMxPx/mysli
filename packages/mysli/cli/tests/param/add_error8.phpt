--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s', ['type' => 'int', 'default' => 'hello']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\input' with message 'Invalid default value for type `int`. Require integer value.' %a
