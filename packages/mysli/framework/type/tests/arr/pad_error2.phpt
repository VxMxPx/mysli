--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr as arr;
arr::pad([], 12, 2, 12);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
