--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
var_dump(arr::implode_keys([40, 50], []));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message %a
