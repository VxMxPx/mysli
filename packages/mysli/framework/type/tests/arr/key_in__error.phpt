--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::key_in(['hi'], null));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
