<?php

#: Before
route::t__reset();

#: Test Basic
#: Expect String /r/post\.html
$route = '/r/post.html';
return route::t__resolve_route($route);

#: Test With Parameters
#: Expect String /r/([0-9]+)/([a-z0-9_\+\-]+)\.html
$route = '/r/<year:digit>/<post:slug>.html';
return route::t__resolve_route($route);

#: Test Costume Regex
#: Expect String /r/([a-f]{6})\.html
$route = '/r/<post:#[a-f]{6}#>.html';
return route::t__resolve_route($route);

#: Test With Optional
#: Expect String "/r/?(?>([a-z0-9_\+\-]+)\.html)?"
$route = '/r/<post?:slug>.html';
return route::t__resolve_route($route);

#: Test With Optional Middle
#: Expect String "/r/?(?>([a-z0-9_\+\-]+)\.html)?/?(?>page\.html)?"
$route = '/r/<post?:slug>.html/page.html';
return route::t__resolve_route($route);
