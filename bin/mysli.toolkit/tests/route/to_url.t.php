<?php

#: Before
route::t__reset();

#: Define Data
route::t__add_data("HIGH:
v.p::index GET *index
v.p::error ANY *error
MEDIUM:
v.p::page     ANY /r/page.html
v.p::params   ANY /r/<year:digit>/<post:slug>.html
v.p::optional ANY /r/<post?:slug>.html
v.p::regex    ANY /r/<post:#[a-f0-9]{6}#>.html
LOW:");

#: Test Basic
#: Use Data
#: Expect String /r/page.html
return route::to_url('v.p::page', []);

#: Test With Parameters
#: Use Data
#: Expect String /r/2016/hell0-world.html
return route::to_url('v.p::params', [ 'year' => 2016, 'post' => 'hell0-world' ]);

#: Test With Optional
#: Use Data
#: Expect String /r/hell0-world.html
return route::to_url('v.p::optional', [ 'post' => 'hell0-world' ]);

#: Test With Optional Not Provided
#: Use Data
#: Expect String /r
return route::to_url('v.p::optional', []);

#: Test Costume Regex
#: Use Data
#: Expect String /r/005fff.html
return route::to_url('v.p::regex', [ 'post' => '005fff' ]);

#: Test Missing
#: Use Data
#: Expect Exception mysli\toolkit\exception\route Parameter not found*
return route::to_url('v.p::params', []);

#: Test No Match
#: Use Data
#: Expect Exception mysli\toolkit\exception\route Parameter value is invalid*
return route::to_url('v.p::params', [ 'year' => 'string', 'post' => 12 ]);
