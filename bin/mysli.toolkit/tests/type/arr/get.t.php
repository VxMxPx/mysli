<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [
    'name'       => 'Slovenia',
    'capital'    => 'Ljubljana',
    'area'       => 20273,
    'population' => 2061085,
    'hdi'        => 0.874
];

#: Test Basic
#: Use Data
#: Expect String "Slovenia"
return arr::get($data, 'name');

#: Test Non-existent
#: Use Data
#: Expect Null
return arr::get($data, 'language');

#: Test Non-existent, Default
#: Use Data
#: Expect String "Slovene"
return arr::get($data, 'language', 'Slovene');
