<?php

#: Before
route::t__reset();

#: Define Data
route::t__add_data("HIGH:
mysli.toolkit.root.tests.route.dummy_exec::no_param ANY /r/index.html
mysli.toolkit.root.tests.route.dummy_exec::param    ANY /r/<post:slug>.html
MEDIUM:
LOW:");
// Fake request method
$_SERVER['REQUEST_METHOD'] = 'GET';

#: Test Execute No Params
#: Use Data
#: Expect True
return route::execute('/r/index.html');

#: Test Execute Param
#: Use Data
#: Expect True
return route::execute('/r/hello-world.html');

#: Test Execute Invalid Type Param
#: Use Data
#: Expect False
return route::execute('/r/hello-world!.html');
