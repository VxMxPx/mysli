--TEST--
Complex nesting.
--FILE--
<?php
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
level1a : 1a
level1b :
    level2a:
        level3a: 3a
        level3b: 3b
    level2b: 2b
    level2c:
        level3a: 3a
        level3b:
            - one
            - two
            - three
            - four
    level2d:
        - one
        - two
level1c:
    level2a: 2b
EOT
));

?>
--EXPECT--
Array
(
    [level1a] => 1a
    [level1b] => Array
        (
            [level2a] => Array
                (
                    [level3a] => 3a
                    [level3b] => 3b
                )

            [level2b] => 2b
            [level2c] => Array
                (
                    [level3a] => 3a
                    [level3b] => Array
                        (
                            [0] => one
                            [1] => two
                            [2] => three
                            [3] => four
                        )

                )

            [level2d] => Array
                (
                    [0] => one
                    [1] => two
                )

        )

    [level1c] => Array
        (
            [level2a] => 2b
        )

)
