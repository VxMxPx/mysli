--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
$arr = [];
arr_path::set($arr, null, null);
print_r($arr);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
