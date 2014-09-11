--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr as arr;
$ar1 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
$ar2 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
print_r(arr::merge($ar1, $ar2));
print_r(arr::merge($ar1, $ar2, arr::merge_all));
?>
--EXPECT--
Array
(
    [0] => 0
    [1] => 1
    [2] => 2
    [3] => 3
    [4] => 0
    [5] => 1
    [6] => 2
    [7] => 3
)
Array
(
    [255] => 0
    [256] => 1
    [257] => 2
    [258] => 3
)
