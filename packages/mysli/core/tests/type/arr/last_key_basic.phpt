--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
var_dump(arr::last_key([12 => 'hello', 13 => 'world']));
var_dump(arr::last_key([]));
?>
--EXPECT--
int(13)
NULL
