--TEST--
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(<<<EOF
name: bar
require: []
line: foo
EOF
));
?>
--EXPECT--
Array
(
    [name] => bar
    [require] => Array
        (
        )

    [line] => foo
)
