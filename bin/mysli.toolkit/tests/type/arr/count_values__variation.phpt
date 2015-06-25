--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$values = ['Domžale', 30, 'Škofja Loka', 30, 'Šoštanj', 'Domžale', 'škofja Loka', 30];
print_r(arr::count_values($values, false));
?>
--EXPECT--
Array
(
    [domžale] => 2
    [30] => 3
    [škofja loka] => 2
    [šoštanj] => 1
)