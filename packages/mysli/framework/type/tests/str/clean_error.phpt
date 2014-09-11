--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

str::clean('21', '');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
