--TEST--
Translate the key, using particular language.
--FILE--
<?php
use mysli\util\i18n\translator;

$t = new translator([
    'us' => [
        '.meta' => [
            'created_on' => 20140930,
            'modified'   => 20140930
        ],
        'HELLO' => [
            'value' => 'Hello!'
        ]
    ],
    'si' => [
        '.meta' => [
            'created_on' => 20140930,
            'modified'   => 20140930
        ],
        'HELLO' => [
            'value' => 'Zdravo!'
        ]
    ]
    ], 'si', 'us');

print_r($t->translate_as('HELLO', 'si'));
?>
--EXPECT--
Zdravo!
