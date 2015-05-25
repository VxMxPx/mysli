--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

print_r(str::split('hello_world', '_'));
print_r(str::split('hello_world_and_moon', '_', 2));

?>
--EXPECT--
Array
(
    [0] => hello
    [1] => world
)
Array
(
    [0] => hello
    [1] => world_and_moon
)
