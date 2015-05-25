--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
arr::merge([]);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
