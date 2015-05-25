--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

print_r(str::split_trim('   one,two,  three    , four   ', ','));
print_r(str::split_trim('   one,two,  three    , four   ', ',', 2));
print_r(
    str::split_trim('  // one,two// , /  three  , four //  ', ',', null, '/ '));

?>
--EXPECT--
Array
(
    [0] => one
    [1] => two
    [2] => three
    [3] => four
)
Array
(
    [0] => one
    [1] => two,  three    , four
)
Array
(
    [0] => one
    [1] => two
    [2] => three
    [3] => four
)
