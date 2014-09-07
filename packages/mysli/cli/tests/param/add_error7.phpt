--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s', ['type' => 'bool', 'default' => 'hello']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'Invalid default value for type `bool`. Require true/false.' %a
