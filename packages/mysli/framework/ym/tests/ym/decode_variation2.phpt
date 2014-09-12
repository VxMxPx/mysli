--TEST--
No key array.
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
- one
- two
- three
EOT
));

?>
--EXPECT--
Array
(
    [0] => one
    [1] => two
    [2] => three
)
