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
$pets = [
    'pets' => [ 'dog', 'cat', 'bunny' ]
];

#: Test Basic
#: Use Data
$modified = $array;
$modified['main']['settings']['timezone'] = 'GMT+2';
arr_path::set($array, 'main.settings.timezone', 'GMT+2');
return assert::equals($array, $modified);

#: Test Non-Associative
#: Use Data
arr_path::set($pets, 'pets', ['turtle', 'piglet']);
return assert::equals(
    $pets,
    [
        'pets' => [ 'turtle', 'piglet', 'bunny' ]
    ]
);

#: Test Specific in Non-Associative
#: Use Data
arr_path::set($pets, 'pets.2', 'rabbit');
return assert::equals(
    $pets,
    [
        'pets' => [ 'dog', 'cat', 'rabbit' ]
    ]
);

#: Test Specific in Non-Associative to Array
#: Use Data
arr_path::set($pets, 'pets.2', ['fluffy', 'bunny']);
return assert::equals(
    $pets,
    [
        'pets' => [ 'dog', 'cat', [ 'fluffy', 'bunny' ] ]
    ]
);

#: Test New Key in Root
#: Use Data
$modified = $array;
$modified['number'] = 42;
arr_path::set($array, 'number', 42);
return assert::equals($array, $modified);

#: Test New Empty Key in Root
#: Use Data
$modified = $array;
$modified[''] = 43;
arr_path::set($array, '', 43);
return assert::equals($array, $modified);

#: Test Modify Multiple by General Key
#: Use Data
$modified = $array;
$modified['main']['settings']['timezone'] = 'GMT+2';
$modified['main']['settings']['debug']    = -1;
arr_path::set($array, 'main.settings', [ 'timezone' => 'GMT+2', 'debug' => -1 ]);
return assert::equals($array, $modified);

#: Test Exception
#: Expect Exception mysli\toolkit\exception\validate
$arr = [];
arr_path::set($arr, [], false);
