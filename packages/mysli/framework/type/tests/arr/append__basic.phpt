--TEST--
--FILE--
<?php
use mysli\framework\type\arr as arr;
$array = [4];
arr::append($array, 5);
print_r($array);
?>
--EXPECT--
Array
(
    [0] => 4
    [1] => 5
)
