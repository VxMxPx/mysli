--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('--s');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'Long argument need to be longer than one character: `s` for `s`.' %a
