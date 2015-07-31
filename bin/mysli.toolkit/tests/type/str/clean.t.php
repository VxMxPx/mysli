<?php

#: Before
use mysli\toolkit\type\str;

#: Define String
$str = 'Hello_World-(12)!!';

#: Test Alpha
#: Use String
#: Expect String "HelloWorld"
return str::clean($str, 'alpha');

#: Test Numeric
#: Use String
#: Expect String "12"
return str::clean($str, 'numeric');

#: Test Alpha-Num
#: Use String
#: Expect String "HelloWorld12"
return str::clean($str, 'alphanum');

#: Test Slug
#: Use String
#: Expect String "Hello_World-12"
return str::clean($str, 'slug');

#: Test Exception on Invalid Mask
#: Expect Exception mysli\toolkit\exception\str 10
return str::clean('Foo', '___');

#: Test Regex
#: Use String
#: Expect String "HelloWorld-12"
return str::clean($str, '<[^a-z0-9\-]>i');
