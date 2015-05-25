--TEST--
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(<<<EOT
level1:
    - Foo: Yes
    - list: item 1
    - list: item 2
EOT
));

?>
--EXPECT--
Array
(
    [level1] => Array
        (
            [0] => Foo: Yes
            [1] => list: item 1
            [2] => list: item 2
        )

)
