--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$data = [
    '  name ',
    ' capital    ',
    ' area  ',
    ' population ',
    'hdi                   ',
];
print_r(arr::trim_values($data));
?>
--EXPECT--
Array
(
    [0] => name
    [1] => capital
    [2] => area
    [3] => population
    [4] => hdi
)
