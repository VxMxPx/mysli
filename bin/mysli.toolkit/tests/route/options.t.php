<?php

#: Before
route::t__reset();

#: Test Default Options Encode Properly
route::t__add_data("HIGH:
vendor.package::index GET *index
vendor.package::error ANY *error
MEDIUM:
vendor.package::page ANY /r/page.html
LOW:");

return assert::equals(route::dump(), [
    'high' => [
        [
            'call'   => 'vendor.package::index',
            'method' => 'GET',
            'route'  => '*index',
        ],
        [
            'call'     => 'vendor.package::error',
            'method'   => 'ANY',
            'route'    => '*error',
        ],
    ],
    'medium' => [
        [
            'call'   => 'vendor.package::page',
            'method' => 'ANY',
            'route'  => '/r/page.html'
        ]
    ]
]);
