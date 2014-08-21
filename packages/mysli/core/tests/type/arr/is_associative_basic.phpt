--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
var_dump(arr::is_associative(['one', 'two', 'three']));
var_dump(arr::is_associative(['one' => 1, 'two' => 2, 'three' => 3]));
?>
--EXPECT--
bool(false)
bool(true)
