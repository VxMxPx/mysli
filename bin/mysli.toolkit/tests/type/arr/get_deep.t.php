<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [
    'countries' =>
    [
        'slovenia' => [
            'name'       => 'Slovenia',
            'capital'    => 'Ljubljana',
            'numeric'    => [
                'area'       => 20273,
                'population' => 2061085,
                'hdi'        => 0.874
            ]
        ]
    ]
];

#: Test Basic
#: Use Data
#: Expect String "Slovenia"
return arr::get_deep($data, ['countries', 'slovenia', 'name']);

#: Test Non Existent
#: Use Data
#: Expect Null
return arr::get_deep($data, ['countries', 'russia']);
