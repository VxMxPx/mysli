--TEST--
Overwrite key.
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
key : String
key :
    - an
    - array
key : 42
EOT
));

?>
--EXPECT--
Array
(
    [key] => 42
)
