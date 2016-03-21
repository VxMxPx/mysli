<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect True
return arr::has_deep(['greetings' => 'hi'], ['greetings']);

#: Test Basic, Numeric
#: Expect True
return arr::has_deep(['hi'], [0]);

#: Test Deep
#: Expect True
return arr::has_deep(
    ['country' => [ 'slovenia' => [ 'name' => 'Slovenia' ] ]],
    ['country', 'slovenia']
);

#: Test Deep, Deep
#: Expect True
return arr::has_deep(
    ['country' => [ 'slovenia' => [ 'name' => 'Slovenia' ] ]],
    ['country', 'slovenia', 'name']
);
