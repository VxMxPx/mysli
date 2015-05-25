--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$data = ['a', 'b', 'c', 'd'];
var_dump(count(arr::get_random($data)));
?>
--EXPECT--
int(1)
