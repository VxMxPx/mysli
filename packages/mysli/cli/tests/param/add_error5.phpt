--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('-s/--long-diff1');
$params->add('-s/--long-diff2');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'Short argument exists: `s` for `long-diff2` defined in `long-diff1`.' %a
