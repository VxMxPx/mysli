--TEST--
Multi-line
--FILE--
<?php
use mysli\util\i18n\parser;

print_r(parser::parse(<<<FILE
# Multi-line
@MULTILINE Hello,
I'm multi-line
text, I'll be converted to one line.

# Multi-line with preserved new-lines
@MULTILINE_KEEP_LINES[nl]
Hello,
the text will stay
in multiple lines!
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

    [MULTILINE] => Array
        (
            [value] => Hello, I'm multi-line text, I'll be converted to one line.
        )

    [MULTILINE_KEEP_LINES] => Array
        (
            [value] => Hello,
the text will stay
in multiple lines!
        )

)
