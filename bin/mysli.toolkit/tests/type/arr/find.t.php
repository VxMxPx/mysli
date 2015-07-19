<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [ 'a' => 40, 'b' => 41, 'c' => 40, 'd' => '40' ];

#: Test Limited
#: Use Data
#: Expect String "a"
return arr::find($data, 40);

#: Test Limited, Type Mismatch
#: Use Data
#: Expect String "b"
return arr::find($data, '41');

#: Test Not limited
#: Use Data
return assert::equals(
    arr::find($data, 40, false),
    ['a', 'c', 'd']
);

#: Test Not limited, Strict
#: Use Data
return assert::equals(
    arr::find($data, 40, false, true),
    ['a', 'c']
);
