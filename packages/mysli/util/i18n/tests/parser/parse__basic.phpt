--TEST--
# Standard translations...
--FILE--
<?php
use mysli\util\i18n\parser;

print_r(parser::parse(<<<FILE
# Standard translations...
@HELLO_WORLD  Hello World!
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

    [HELLO_WORLD] => Array
        (
            [value] => Hello World!
        )

)
