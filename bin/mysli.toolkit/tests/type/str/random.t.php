<?php

#: Before
use mysli\toolkit\type\str;

#: Test Lowercase
#: Expect Integer 1
return preg_match('/[a-z]{10}/', str::random(10, 'a'));

#: Test Lower-upper
#: Expect Integer 1
return preg_match('/[a-z]{10}/i', str::random(10, 'aA'));

#: Test Lower-upper-numeric
#: Expect Integer 1
return preg_match('/[a-z0-9]{10}/i', str::random(10, 'aA1'));

#: Test Lower-upper-numeric-special
#: Expect Integer 1
return preg_match(
    '/[a-z0-9'. preg_quote('~#$%&()=?*<>-_:.;,+!') .']{10}/i',
    str::random(10, 'aA1s')
);
