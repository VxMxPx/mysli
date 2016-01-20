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

#: Test Remove One
#: Use Data
#: Expect Integer 0
route::remove('vendor.package::page');
$routes = route::dump();
return count($routes['medium']);

#: Test Remove All
#: Use Data
route::remove('vendor.package::*');
return assert::equals(route::dump(), [ 'high' => [], 'medium' => [] ]);
