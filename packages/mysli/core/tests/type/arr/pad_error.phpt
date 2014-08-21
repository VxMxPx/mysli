--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
print_r(arr::pad([], '+', 0, arr::pad_right));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message %a
