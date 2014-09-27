--TEST--
Empty file.
--FILE--
<?php
use mysli\framework\ym as ym;

print_r(ym::decode(''));

?>
--EXPECT--
Array
(
)
