--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('--name');
$params->add('--name');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'ID exists: `name`.' %a
