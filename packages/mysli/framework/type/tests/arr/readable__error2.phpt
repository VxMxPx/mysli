--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
arr::readable([], 2, -1);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
