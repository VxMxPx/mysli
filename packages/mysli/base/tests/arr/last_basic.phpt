--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
var_dump(arr::last(['hello', 'world']));
var_dump(arr::last([]));
?>
--EXPECT--
string(5) "world"
NULL
