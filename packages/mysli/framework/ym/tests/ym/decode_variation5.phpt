--TEST--
--FILE--
<?php
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
level1:
    level2:
        level3:
            - one
            - two
-
    -
        - one
        - two
        -
            key : value
EOT
));

?>
--EXPECT--
Array
(
    [level1] => Array
        (
            [level2] => Array
                (
                    [level3] => Array
                        (
                            [0] => one
                            [1] => two
                        )

                )

        )

    [1] => Array
        (
            [0] => Array
                (
                    [0] => one
                    [1] => two
                    [2] => Array
                        (
                            [key] => value
                        )

                )

        )

)
