--TEST--
Empty file.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(''));

?>
--EXPECT--
Array
(
)
