<?php

#: Test Basic
router::add('vendor.blog.controller::method', 'GET:[blog/]post');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    $routes['method@vendor.blog.controller'],
    [
        'to'     => 'vendor.blog.controller::method',
        'rid'    => 'method@vendor.blog.controller',
        'method' => [ 'GET' ],
        'prefix' => 'blog/',
        'route'  => 'GET:[blog/]post',
        'regex'  => '<^blog/post$>i',
        'type'   => 'normal',
        'parameters' => []
    ]
);

#: Test No-prefix
router::add('vendor.blog.controller::method', 'GET:post');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    $routes['method@vendor.blog.controller'],
    [
        'to'     => 'vendor.blog.controller::method',
        'rid'    => 'method@vendor.blog.controller',
        'method' => [ 'GET' ],
        'prefix' => null,
        'route'  => 'GET:post',
        'regex'  => '<^post$>i',
        'type'   => 'normal',
        'parameters' => []
    ]
);

#: Test No-method
router::add('vendor.blog.controller::method', '[blog/]post');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    $routes['method@vendor.blog.controller'],
    [
        'to'     => 'vendor.blog.controller::method',
        'rid'    => 'method@vendor.blog.controller',
        'method' => [ 'GET', 'POST', 'DELETE', 'PUT' ],
        'prefix' => 'blog/',
        'route'  => '[blog/]post',
        'regex'  => '<^blog/post$>i',
        'type'   => 'normal',
        'parameters' => []
    ]
);

#: Test No-method, no prefix
router::add('vendor.blog.controller::post', 'post');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    $routes['post@vendor.blog.controller'],
    [
        'to'     => 'vendor.blog.controller::post',
        'rid'    => 'post@vendor.blog.controller',
        'method' => [ 'GET', 'POST', 'DELETE', 'PUT' ],
        'prefix' => null,
        'route'  => 'post',
        'regex'  => '<^post$>i',
        'type'   => 'normal',
        'parameters' => []
    ]
);

#: Test Regular Expression Buil-in filters
router::add('vendor.blog.controller::method', 'tag/{tag|alpha}/{year|numeric}/{title|alphanum}/{post|slug}/{id|any}');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    [
        $routes['method@vendor.blog.controller']['regex'],
        $routes['method@vendor.blog.controller']['parameters']
    ],
    [
        '<^tag/([a-z]+)/([0-9]+)/([a-z0-9]+)/([a-z0-9_\-]+)/(.*?)$>i',
        ['tag', 'year', 'title', 'post', 'id']
    ]
);

#: Test Regular Expression Costume
router::add('vendor.blog.controller::method', 'tag/{tag|([a-z]{2}\-[a-z]{4})}');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    [
        $routes['method@vendor.blog.controller']['regex'],
        $routes['method@vendor.blog.controller']['parameters']
    ],
    [
        '<^tag/([a-z]{2}\-[a-z]{4})$>i', ['tag']
    ]
);

#: Test Many Segments
router::add('vendor.blog.controller::method', '{path|any}/{page|alpha}.html');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    [
        $routes['method@vendor.blog.controller']['regex'],
        $routes['method@vendor.blog.controller']['parameters']
    ],
    [
        '<^(.*?)/([a-z]+)\.html$>i', [ 'path', 'page' ]
    ]
);

#: Test Any Following
router::add('vendor.blog.controller::method', 'tag/...');
$routes = router::dump(router::route_normal);
router::reset();
return assert::equals(
    [
        $routes['method@vendor.blog.controller']['regex'],
        $routes['method@vendor.blog.controller']['parameters']
    ],
    [
        '<^tag/?.*?$>i', [ ]
    ]
);

#: Test Special
router::add('vendor.blog.controller', 'index', router::route_special);
$routes = router::dump(router::route_special);
router::reset();
return assert::equals(
    $routes['index@vendor.blog.controller'],
    [
        'to'     => 'vendor.blog.controller::index',
        'rid'    => 'index@vendor.blog.controller',
        'method' => [ 'GET', 'POST', 'DELETE', 'PUT' ],
        'prefix' => null,
        'route'  => 'index',
        'regex'  => null,
        'type'   => 'special',
        'parameters' => []
    ]
);

#: Test Before, No Route
router::add('vendor.blog.controller::method', null, router::route_before);
$routes = router::dump(router::route_before);
router::reset();
return assert::equals(
    $routes,
    [
        'method@vendor.blog.controller' =>
        [
            'to'     => 'vendor.blog.controller::method',
            'rid'    => 'method@vendor.blog.controller',
            'method' => [ 'GET', 'POST', 'DELETE', 'PUT' ],
            'prefix' => null,
            'route'  => null,
            'regex'  => null,
            'type'   => 'before',
            'parameters' => []
        ]
    ]
);
