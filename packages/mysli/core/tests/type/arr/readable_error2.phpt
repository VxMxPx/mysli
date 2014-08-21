--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
arr::readable([], 2, -1);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message %a
