--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
var_dump(arr::first(['hello', 'world']));
var_dump(arr::first([]));
?>
--EXPECT--
string(5) "hello"
NULL
