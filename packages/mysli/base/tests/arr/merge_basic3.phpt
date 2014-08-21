--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
$ar1 = [0, 1, 2, 3];
$ar2 = [0, 1, 2, 3];
print_r(arr::merge($ar1, $ar2));
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
