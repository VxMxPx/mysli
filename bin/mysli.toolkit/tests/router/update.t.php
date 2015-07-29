<?php

#: Before
router::reset();
router::add('vendor.blog.controller::post', 'GET:[blog/]{post|slug}.html');

#: Define Route
$route = router::get('post@vendor.blog.controller');
$route = array_pop($route);

#: Test New Value
#: Use Route
#: Expect String "Yes!"
router::update($route, 'option', 'Yes!');
$route = router::dump(router::route_normal);
$route = array_pop($route);
return $route['option'];

#: Test To
#: Use Route
// Define modified route...
$mod_route = $route;
$mod_route['rid'] = 'pst@new.blog.ctl';
$mod_route['to'] = 'new.blog.ctl::pst';
$mod_route = [
    $mod_route['rid'] => $mod_route
];
router::update($route, 'to', 'new.blog.ctl::pst');
$route = router::dump(router::route_normal);
return assert::equals( $route, $mod_route );

#: Test Route
#: Use Route
// Define modified route...
$mod_route = $route;
$mod_route['route'] = 'GET|POST:[r/]{postx|([a-z0-9]+)}.htm';
$mod_route['regex'] = '<^r/([a-z0-9]+)\.htm$>i';
$mod_route['method'] = ['GET', 'POST'];
$mod_route['prefix'] = 'r/';
$mod_route['parameters'] = [ 'postx' ];
$mod_route = [
    $mod_route['rid'] => $mod_route
];
router::update($route, 'route', 'GET|POST:[r/]{postx|([a-z0-9]+)}.htm');
$route = router::dump(router::route_normal);
return assert::equals( $route, $mod_route );

#: Test Method
#: Use Route
// Define modified route...
$mod_route = $route;
$mod_route['route'] = 'DELETE|PUT:[blog/]{post|slug}.html';
$mod_route['method'] = ['DELETE', 'PUT'];
$mod_route = [
    $mod_route['rid'] => $mod_route
];
router::update($route, 'method', ['DELETE', 'PUT']);
$route = router::dump(router::route_normal);
return assert::equals( $route, $mod_route );

#: Test Prefix
#: Use Route
// Define modified route...
$mod_route = $route;
$mod_route['route'] = 'GET:[r/]{post|slug}.html';
$mod_route['regex'] = '<^r/([a-z0-9_\-]+)\.html$>i';
$mod_route['prefix'] = 'r/';
$mod_route = [
    $mod_route['rid'] => $mod_route
];
router::update($route, 'prefix', 'r/');
$route = router::dump(router::route_normal);
return assert::equals( $route, $mod_route );
