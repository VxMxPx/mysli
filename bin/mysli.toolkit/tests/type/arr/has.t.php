<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect True
return arr::has(['greetings' => 'hi'], 'greetings');

#: Test Basic, Numeric
#: Expect True
return arr::has(['hi'], 0);

#: Test Non-existent
#: Expect False
return arr::has(['greetings' => 'hi', 'foo' => 'bar'], -1);

#: Test Multiple
#: Expect True
return arr::has(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'mail']
);

#: Test Multiple, One Missing
#: Expect False
return arr::has(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'address']);
