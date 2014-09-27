--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::is_associative(['one', 'two', 'three']));
var_dump(arr::is_associative(['one' => 1, 'two' => 2, 'three' => 3]));
?>
--EXPECT--
bool(false)
bool(true)
