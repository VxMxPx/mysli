--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
print_r(arr::pad([], '+', 0, arr::pad_right));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
