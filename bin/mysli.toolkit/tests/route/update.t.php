<?php

#: Before
route::t__reset();

#: Define Data
route::t__add_data("HIGH:
MEDIUM:
vendor.package::page ANY /r/page.html
LOW:");

#: Test Update One
#: Use Data
route::update('vendor.package::page', [
    'method' => 'GET'
]);
$routes = route::dump();
return assert::equals($routes['medium'], [
    [
        'call'   => 'vendor.package::page',
        'route'  => '/r/page.html',
        'method' => 'GET',
    ]
]);

#: Test Update, Change Priority
#: Use Data
route::update('vendor.package::page', [
    'method'   => 'GET',
    'priority' => 'low'
]);
return assert::equals($routes = route::dump(), [
    'medium' => [],
    'low'    => [
        [
            'call'   => 'vendor.package::page',
            'method' => 'GET',
            'route'  => '/r/page.html',
        ]
    ]
]);
