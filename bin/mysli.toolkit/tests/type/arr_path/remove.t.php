<?php

#: Before
use mysli\toolkit\type\arr_path;

#: Define Data
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT',
            'debug'    => true
        ]
    ]
];

#: Test Basic
#: Use Data
arr_path::remove($array, 'main.settings.timezone');
return assert::equals(
    $array,
    [
        'main' => [
            'settings' => [
                'debug' => true
            ]
        ]
    ]
);

#: Test Remove Array
#: Use Data
arr_path::remove($array, 'main.settings');
return assert::equals(
    $array,
    [
        'main' => []
    ]
);

#: Test Remove Nothing
#: Use Data
$original = $array;
arr_path::remove($array, '');
return assert::equals(
    $array,
    $original
);

#: Test Non-Existent Key
#: Use Data
$original = $array;
arr_path::remove($array, 'main.not_found');
return assert::equals(
    $array,
    $original
);
