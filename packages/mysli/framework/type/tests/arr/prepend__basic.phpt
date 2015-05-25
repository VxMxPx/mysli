--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$array = [4];
arr::prepend($array, 5);
print_r($array);
?>
--EXPECT--
Array
(
    [0] => 5
    [1] => 4
)
