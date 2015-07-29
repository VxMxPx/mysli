<?php

#: Before
router::reset();
router::add('vendor.blog.controller::post', 'GET:[blog/](post|slug).html');

#: Test Basic
#: Expect String "GET:[blog/](post|slug).html"
$route = router::get('post@vendor.blog.controller');
return $route['normal:post@vendor.blog.controller']['route'];

#: Test Regex
#: Expect String "GET:[blog/](post|slug).html"
$route = router::get('*@vendor.blog.controller');
return $route['normal:post@vendor.blog.controller']['route'];

#: Test Exception on Missing @
#: Expect Exception mysli\toolkit\exception\router 10
router::get('foo.bar');
