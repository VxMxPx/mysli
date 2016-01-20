<?php

#: Before
route::t__reset();

#: Define Data
route::t__add_data("HIGH:
vendor.package::index GET *index
vendor.package::error ANY *error
MEDIUM:
vendor.package::page ANY /r/page.html
LOW:");

#: Test Get One
#: Use Data
return assert::equals(route::get('vendor.package::index'), [
        // Priority
        'high',
        // Index
        0,
        // Data
        [
            'call'   => 'vendor.package::index',
            'method' => 'GET',
            'route'  => '*index'
        ]
]);
