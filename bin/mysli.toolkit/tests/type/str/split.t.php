<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
return assert::equals(
    str::split('hello_world', '_'),
    [ 'hello', 'world' ]
);

#: Test Limited
return assert::equals(
    str::split('hello_world_and_moon', '_', 2),
    [ 'hello', 'world_and_moon' ]
);
