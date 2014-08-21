--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$array1 = array("color" => "red", 2, 4);
$array2 = array("a", "b", "color" => "green", "shape" => "trapezoid", 4);
print_r(arr::merge($array1, $array2));
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
