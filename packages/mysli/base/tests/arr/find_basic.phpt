--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
$data = [
    'a' => 40,
    'b' => 41,
    'c' => 40,
    'd' => '40'
];
var_dump(arr::find($data, '41'));
var_dump(arr::find($data, 40));
print_r(arr::find($data, 40, false));
print_r(arr::find($data, 40, false, true));
?>
--EXPECT--
string(1) "b"
string(1) "a"
Array
(
    [0] => a
    [1] => c
    [2] => d
)
Array
(
    [0] => a
    [1] => c
)
