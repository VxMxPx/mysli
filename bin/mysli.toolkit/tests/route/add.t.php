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

#: Test Add to Empty
#: Use Data
route::add('vendor.package::post', 'GET', '/r/post.html', 'low');
$routes = route::dump();
return assert::equals($routes['low'], [
        [
            'call'   => 'vendor.package::post',
            'method' => 'GET',
            'route'  => '/r/post.html'
        ]
]);

#: Test Add, Bottom
#: Use Data
route::add('vendor.package::post', 'GET', '/r/post.html', 'medium');
$routes = route::dump();
return assert::equals($routes['medium'], [
        [
            'call'   => 'vendor.package::page',
            'method' => 'ANY',
            'route'  => '/r/page.html'
        ],
        [
            'call'   => 'vendor.package::post',
            'method' => 'GET',
            'route'  => '/r/post.html'
        ]
]);

#: Test Add, Top
#: Use Data
route::add('vendor.package::post', 'GET', '/r/post.html', 'medium', true);
$routes = route::dump();
return assert::equals($routes['medium'], [
        [
            'call'   => 'vendor.package::post',
            'method' => 'GET',
            'route'  => '/r/post.html'
        ],
        [
            'call'   => 'vendor.package::page',
            'method' => 'ANY',
            'route'  => '/r/page.html'
        ],
]);
