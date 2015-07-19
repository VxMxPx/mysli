<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect True
return arr::key_in(['greetings' => 'hi'], 'greetings');

#: Test Basic, Numeric
#: Expect True
return arr::key_in(['hi'], 0);

#: Test Non-existent
#: Expect False
return arr::key_in(['greetings' => 'hi', 'foo' => 'bar'], -1);

#: Test Multiple
#: Expect True
return arr::key_in(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'mail']
);

#: Test Multiple, One Missing
#: Expect False
return arr::key_in(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'address']);

#: Test Exception, Invalid Key
#: Expect Exception mysli\toolkit\exception\validate
arr::key_in(['hi'], null);
