--TEST--
Spacing
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(<<<EOT
k1 : value

k2 : value

k3 : value
EOT
));

?>
--EXPECT--
Array
(
    [k1] => value
    [k2] => value
    [k3] => value
)
