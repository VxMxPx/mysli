--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::first_key([12 => 'hello', 13 => 'world']));
var_dump(arr::first_key([]));
?>
--EXPECT--
int(12)
NULL
