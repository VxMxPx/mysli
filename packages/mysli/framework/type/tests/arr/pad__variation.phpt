--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$array = [
    '#', '#', '#'
];
print_r(arr::pad($array, '+', 3, arr::pad_right));
print_r(arr::pad($array, '+', 3, arr::pad_left));
print_r(arr::pad($array, '+', 3, arr::pad_both));
?>
--EXPECT--
Array
(
    [0] => #
    [1] => #
    [2] => #
)
Array
(
    [0] => #
    [1] => #
    [2] => #
)
Array
(
    [0] => #
    [1] => #
    [2] => #
)
