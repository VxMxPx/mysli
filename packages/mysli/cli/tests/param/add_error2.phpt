--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add('--name');
$params->add('--name');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message 'ID exists: `name`.' %a
