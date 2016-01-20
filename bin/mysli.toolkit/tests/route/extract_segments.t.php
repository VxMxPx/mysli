<?php

#: Before
route::t__reset();

#: Test Extract No Parameters
$route = '/r/post.html';
return assert::equals(route::t__extract_segments($route), [
    '/r/post.html', []
]);

#: Test Extract Parameters
$route = '/r/<year:digit>/<post:slug>.html';
return assert::equals(route::t__extract_segments($route), [
    '/r/"SEG_year"/"SEG_post".html',
    [
        'year' => [ false, "[0-9]+" ],
        'post' => [ false, "[a-z0-9_\+\-]+" ],
    ]
]);

#: Test Extract Optional
$route = '/r/<post?:slug>.html';
return assert::equals(route::t__extract_segments($route), [
    '/r/"SEG_post".html',
    [
        'post' => [ true, "[a-z0-9_\+\-]+" ],
    ]
]);

#: Test Costume Regex
$route = '/r/<post?:#[a-f]{6}#>.html';
return assert::equals(route::t__extract_segments($route), [
    '/r/"SEG_post".html',
    [
        'post' => [ true, "[a-f]{6}" ],
    ]
]);
