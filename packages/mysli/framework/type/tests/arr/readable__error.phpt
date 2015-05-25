--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
arr::readable([], null);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
