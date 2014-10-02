--TEST--
Number ranges
--FILE--
<?php
use mysli\util\i18n\parser;

print_r(parser::parse(<<<FILE
# Number ranges
@AGE[0...1]   Hopes
@AGE[2...3]   Will
@AGE[4]       Purpose
@AGE[5...12]  Competence
@AGE[13...19] Fidelity
@AGE[20...39] Love
@AGE[40...64] Care
@AGE[65+]     Wisdom
FILE
));

?>
--EXPECTF--
Array
(
    [.meta] => Array
        (
            [created_on] => %d
            [modified] =>%s
        )

    [AGE] => Array
        (
            [0...1] => Array
                (
                    [value] => Hopes
                )

            [2...3] => Array
                (
                    [value] => Will
                )

            [4] => Array
                (
                    [value] => Purpose
                )

            [5...12] => Array
                (
                    [value] => Competence
                )

            [13...19] => Array
                (
                    [value] => Fidelity
                )

            [20...39] => Array
                (
                    [value] => Love
                )

            [40...64] => Array
                (
                    [value] => Care
                )

            [65+] => Array
                (
                    [value] => Wisdom
                )

        )

)
