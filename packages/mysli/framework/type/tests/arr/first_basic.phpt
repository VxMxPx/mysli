--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::first(['hello', 'world']));
var_dump(arr::first([]));
?>
--EXPECT--
string(5) "hello"
NULL
