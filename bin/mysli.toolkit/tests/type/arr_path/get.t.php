<?php

#: Before
use mysli\toolkit\type\arr_path;

#: Define Data
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT',
            'debug'    => true
        ],
        'pets' => [
            'bunny',
            'cat' => [ 'Riki', 'Zitalik' ],
            'dog'
        ]
    ]
];

#: Test Boolean Value
#: Use Data
#: Expect True
return arr_path::get($array, 'main.settings.debug');

#: Test String Value
#: Use Data
#: Expect String "GMT"
return arr_path::get($array, 'main.settings.timezone');

#: Test Array Value
#: Use Data
return assert::equals(
    arr_path::get($array, 'main.settings'),
    [ 'timezone' => 'GMT', 'debug' => true ]
);

#: Test Missing, Default
#: Use Data
#: Expect String "Default"
return arr_path::get($array, 'main.settings.not_there', 'Default');

#: Test Numeric
#: Use Data
#: Expect String "bunny"
return arr_path::get($array, 'main.pets.0');

#: Test Numeric, Deeper
#: Use Data
#: Expect String "Zitalik"
return arr_path::get($array, 'main.pets.cat.1');

#: Test Empty
#: Expect Null
return arr_path::get([], 'main.pets.cat.0');

#: Test Exception, Invalid Path
#: Expect Exception mysli\toolkit\exception\validate
return arr_path::get([], []);
