<?php

#: Before
use mysli\i18n\translator;

#: Define Data
$data = [
    'en-us' => [
        '.meta' => [
            'created_on' => 20140930,
            'updated_on' => 20140930
        ],
        'HELLO_WORLD' => [
            'value' => 'Hello World!'
        ]
    ]
];

#: Test Exists
#: Use Data
#: Expect Integer 1
$translator = new translator('en-us', null);
$translator->append($data);
return $translator->exists('en-us');

#: Test Exists Not
#: Use Data
#: Expect Null
$translator = new translator('en-us', null);
$translator->append($data);
return $translator->exists('sl');
