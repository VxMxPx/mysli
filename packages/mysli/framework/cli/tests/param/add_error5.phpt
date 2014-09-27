--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s/--long-diff1');
$params->add('-s/--long-diff2');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Short argument exists: `s` for `long-diff2` defined in `long-diff1`.' %a
