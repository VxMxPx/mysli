--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$values = ['Maribor', 'koper', 'Ptuj', 'Maribor', 'Koper'];
print_r(arr::count_values($values));
print_r(arr::count_values($values, false));
?>
--EXPECT--
Array
(
    [Maribor] => 2
    [koper] => 1
    [Ptuj] => 1
    [Koper] => 1
)
Array
(
    [maribor] => 2
    [koper] => 2
    [ptuj] => 1
)
