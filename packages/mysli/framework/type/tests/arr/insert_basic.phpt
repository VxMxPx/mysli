--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;

$array = [4, 6];
arr::insert($array, 5, 1);
print_r($array);

$array = [4, 6];
arr::insert($array, 5, 10);
print_r($array);

$array = [4, 5, 6, 7, 9];
arr::insert($array, 8, -1);
print_r($array);


?>
--EXPECT--
Array
(
    [0] => 4
    [1] => 5
    [2] => 6
)
Array
(
    [0] => 4
    [1] => 6
    [2] => 5
)
Array
(
    [0] => 4
    [1] => 5
    [2] => 6
    [3] => 7
    [4] => 8
    [5] => 9
)
