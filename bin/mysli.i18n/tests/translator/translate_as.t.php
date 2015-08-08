<?php

#: Before
use mysli\i18n\translator;

#: Define Translator
$data = [
    'en-us' => [
        '.meta' => [
            'created_on' => 20140930,
            'updated_on' => 20140930
        ],
        'HELLO' => [
            'value' => 'Hello!'
        ]
    ],
    'sl' => [
        '.meta' => [
            'created_on' => 20140930,
            'updated_on' => 20140930
        ],
        'HELLO' => [
            'value' => 'Pozdravljeni!'
        ]
    ]
];
$translator = new translator('en-us', null);
$translator->append($data);

#: Test As en-us
#: Use Translator
#: Expect String "Hello!"
return $translator->translate_as('HELLO', 'en-us');

#: Test As sl
#: Use Translator
#: Expect String "Pozdravljeni!"
return $translator->translate_as('HELLO', 'sl');
