--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
print_r(arr::delete_by_value([], 42));
?>
--EXPECT--
Array
(
)
