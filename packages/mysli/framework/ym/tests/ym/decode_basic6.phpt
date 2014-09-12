--TEST--
Spacing
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
# Comment 1
"#k1" : value
# Comment 2
"#k2" : value
# Comment 3
"#k3" : value
EOT
));

?>
--EXPECT--
Array
(
    [#k1] => value
    [#k2] => value
    [#k3] => value
)
