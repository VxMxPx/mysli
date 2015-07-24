<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
return assert::equals(
    str::split_trim('   one,two,  three    , four   ', ','),
    [ 'one', 'two', 'three', 'four' ]
);

#: Test Limit
return assert::equals(
    str::split_trim('   one,two,  three    , four   ', ',', 2),
    [ 'one', 'two,  three    , four', ]
);

#: Test Special Characters
return assert::equals(
    str::split_trim('  // one,two// , /  three  , four //  ', ',', null, '/ '),
    [ 'one', 'two', 'three', 'four' ]
);
