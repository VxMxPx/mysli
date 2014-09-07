--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('POSITIONAL', ['type' => 'bool']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'Positional arguments cannot have (bool) type `positional`.' %a
