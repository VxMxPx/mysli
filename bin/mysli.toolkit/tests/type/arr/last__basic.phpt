--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::last(['hello', 'world']));
var_dump(arr::last([]));
?>
--EXPECT--
string(5) "world"
NULL