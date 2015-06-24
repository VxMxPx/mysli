--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('POSITIONAL', ['type' => 'bool']);

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Positional arguments cannot have (bool) type `positional`.' %a
