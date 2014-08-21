--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
var_dump(arr::key_in(['hi'], null));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\base\exception\argument' with message %a
