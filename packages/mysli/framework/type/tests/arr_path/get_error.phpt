--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
arr_path::get([], []);
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
