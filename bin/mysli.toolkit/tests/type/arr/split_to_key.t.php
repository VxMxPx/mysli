<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [
    'name.Slovenia',
    'capital.Ljubljana',
    'area.20273',
    'population.2061085',
    'hdi.0.874',
    'missing'
];

#: Test Basic
#: Use Data
return assert::equals(
    arr::split_to_key($data, '.'),
    [
        'name'       => 'Slovenia',
        'capital'    => 'Ljubljana',
        'area'       => '20273',
        'population' => '2061085',
        'hdi'        => '0.874',
    ]
);

#: Test Basic, Include Missing Key
#: Use Data
return assert::equals(
    arr::split_to_key($data, '.', false),
    [
        'name'       => 'Slovenia',
        'capital'    => 'Ljubljana',
        'area'       => '20273',
        'population' => '2061085',
        'hdi'        => '0.874',
        'missing'
    ]
);
