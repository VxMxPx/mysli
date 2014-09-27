--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$array1 = array("color" => "red", 2, 4);
$array2 = array("a", "b", "color" => "green", "shape" => "trapezoid", 4);
print_r(arr::merge($array1, $array2));
print_r(arr::merge($array1, $array2, arr::merge_all));
?>
--EXPECT--
Array
(
    [color] => green
    [0] => a
    [1] => b
    [shape] => trapezoid
    [2] => 4
)
Array
(
    [color] => green
    [0] => a
    [1] => b
    [shape] => trapezoid
    [2] => 4
)
