--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
$array = [
    '#', '#', '#'
];
print_r(arr::pad($array, '+', 6, arr::pad_right));
print_r(arr::pad($array, '+', 6, arr::pad_left));
print_r(arr::pad($array, '+', 6, arr::pad_both));
?>
--EXPECT--
Array
(
    [0] => +
    [1] => +
    [2] => +
    [3] => #
    [4] => #
    [5] => #
)
Array
(
    [0] => #
    [1] => #
    [2] => #
    [3] => +
    [4] => +
    [5] => +
)
Array
(
    [0] => +
    [1] => +
    [2] => #
    [3] => #
    [4] => #
    [5] => +
)
