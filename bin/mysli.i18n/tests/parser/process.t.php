<?php

#: Before
use mysli\i18n\parser;

#: Test Basic
$processed = parser::process(<<<'LANG'
# Standard translations...
@HELLO_WORLD  Hello World!
LANG
);
unset($processed['.meta']);

return assert::equals(
    $processed,
    [
        'HELLO_WORLD' => [ 'value' => 'Hello World!' ]
    ]
);
