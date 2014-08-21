--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
$data = [
    'name.Slovenia',
    'capital.Ljubljana',
    'area.20273',
    'population.2061085',
    'hdi.0.874',
    'missing'
];
print_r(arr::split_to_key($data, '.'));
print_r(arr::split_to_key($data, '.', false));
?>
--EXPECT--
Array
(
    [name] => Slovenia
    [capital] => Ljubljana
    [area] => 20273
    [population] => 2061085
    [hdi] => 0.874
)
Array
(
    [name] => Slovenia
    [capital] => Ljubljana
    [area] => 20273
    [population] => 2061085
    [hdi] => 0.874
    [0] => missing
)
