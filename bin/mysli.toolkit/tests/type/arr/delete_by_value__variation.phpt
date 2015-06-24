--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
print_r(arr::delete_by_value([], 42));
?>
--EXPECT--
Array
(
)
