<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [41, 42, 43, '42', 42];

#: Test Standard, Limited to First Element
#: Use Data
return assert::equals(
    arr::delete_by_value($data, 42),
    [ 0 => 41, 2 => 43, 3 => '42', 4 => 42]
);

#: Test Standard, No Limit
#: Use Data
return assert::equals(
    arr::delete_by_value($data, 42, false),
    [ 0 => 41, 2 => 43]
);


#: Test Standard, No Limit, Strict
#: Use Data
return assert::equals(
    arr::delete_by_value($data, 42, false, true),
    [ 0 => 41, 2 => 43, 3 => '42']
);

#: Test, An Empty Array
return assert::equals(
    arr::delete_by_value([], 42),
    []
);
