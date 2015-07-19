<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Basic
$array = [ '#', '#', '#' ];

#: Test Basic Right
#: Use Basic
return assert::equals(
    arr::pad($array, '+', 6, arr::pad_right),
    [ '+', '+', '+', '#', '#', '#' ]
);

#: Test Basic Left
#: Use Basic
return assert::equals(
    arr::pad($array, '+', 6, arr::pad_left),
    [ '#', '#', '#', '+', '+', '+' ]
);

#: Test Basic Both
#: Use Basic
return assert::equals(
    arr::pad($array, '+', 6, arr::pad_both),
    [ '+', '+', '#', '#', '#', '+' ]
);

#: Test Already on Full Lengh
#: Use Basic
return assert::equals(
    arr::pad($array, '+', 3, arr::pad_right),
    [ '#', '#', '#' ]
);

#: Test Exception, For Size Expecting an Integer
#: Expect Exception mysli\toolkit\exception\validate
arr::pad([], '+', ':<');

#: Test Exception, Invalid Type
#: Expect Exception mysli\toolkit\exception\arr 10
arr::pad([], '+', 2, 3);
