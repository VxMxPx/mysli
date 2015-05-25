--TEST--
Variables
--FILE--
<?php
use mysli\util\i18n\parser;

print_r(parser::parse(<<<FILE
# Variables
@GREETING              Hi there, {1}!
@GREETING_AND_AGE      Hi there, {1} you're {2} years old.
@GREETING_AND_REGISTER Hi there, please {1 login} or {2 register}.
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

    [GREETING] => Array
        (
            [value] => Hi there, {1}!
        )

    [GREETING_AND_AGE] => Array
        (
            [value] => Hi there, {1} you're {2} years old.
        )

    [GREETING_AND_REGISTER] => Array
        (
            [value] => Hi there, please {1 login} or {2 register}.
        )

)
