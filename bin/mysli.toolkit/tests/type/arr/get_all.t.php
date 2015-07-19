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
return assert::equals(
    arr::get_all($data, 'name'),
    [ 'Slovenia' ]
);

#: Test Basic, Multiple by String
#: Use Data
return assert::equals(
    arr::get_all($data, 'name,capital'),
    ['Slovenia', 'Ljubljana']
);

#: Test Basic, Multiple by Array
#: Use Data
return assert::equals(
    arr::get_all($data, ['name', 'area', 'population']),
    [ 'Slovenia', 20273, 2061085 ]
);

#: Test Non-existent
#: Use Data
return assert::equals(
    arr::get_all($data, 'language'),
    [ Null ]
);

#: Test Non-existent, Default
#: Use Data
return assert::equals(
    arr::get_all($data, 'language', 'Slovene'),
    ['Slovene']
);

#: Test Multiple Non-existent, Defaults
#: Use Data
return assert::equals(
    arr::get_all($data, 'language,calling_code,tld', ['Slovene', '+386', '.si']),
    [ 'Slovene', '+386', '.si' ]
);

#: Test Multiple Non-existent, Defaults, Missing
#: Use Data
return assert::equals(
    arr::get_all($data, 'language,calling_code,tld', ['Slovene']),
    [ 'Slovene', null, null ]
);

#: Test Multiple Non-existent, One For All Default
#: Use Data
return assert::equals(
    arr::get_all($data, 'language,calling_code,tld', 'N/A'),
    [ 'N/A', 'N/A', 'N/A' ]
);
