--TEST--
Empty file.
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\ym as ym;

print_r(ym::decode(''));

?>
--EXPECT--
Array
(
)
