<?php

#: Before
use mysli\i18n\parser;

#: Test Basic
$processed = parser::process(<<<'LANG'
# Multi-line
@MULTILINE Hello,
I'm multi-line
text, I'll be converted to one line.

# Multi-line with preserved new-lines
@MULTILINE_KEEP_LINES[nl]
Hello,
the text will stay
in multiple lines!
LANG
);
unset($processed['.meta']);

return assert::equals(
    $processed,
    [
        'MULTILINE' => [
            'value' => "Hello, I'm multi-line text, I'll be converted to one line."
        ],
        'MULTILINE_KEEP_LINES' => [
            'value' => "Hello,\nthe text will stay\nin multiple lines!"
        ]
    ]
);
