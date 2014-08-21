--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$data = [41, 42, 43, '42', 42];
print_r(arr::delete_by_value($data, 42));
print_r(arr::delete_by_value($data, 42, false));
print_r(arr::delete_by_value($data, 42, false, true));
?>
--EXPECT--
Array
(
    [0] => 41
    [2] => 43
    [3] => 42
    [4] => 42
)
Array
(
    [0] => 41
    [2] => 43
)
Array
(
    [0] => 41
    [2] => 43
    [3] => 42
)
