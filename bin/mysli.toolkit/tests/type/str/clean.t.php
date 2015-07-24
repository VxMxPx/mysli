<?php

#: Before
use mysli\toolkit\type\str;

#: Define String
$str = 'Hello World (12)!!';

#: Test Lowercase
#: Use String
#: Expect String "elloorld"
return str::clean($str, 'a');

#: Test Lower and Upper
#: Use String
#: Expect String "HelloWorld"
return str::clean($str, 'aA');

#: Test Lower, Upper and Numeric
#: Use String
#: Expect String "HelloWorld12"
return str::clean($str, 'aA1');

#: Test Lower, Upper, Numeric and Special
#: Use String
#: Expect String "Hello World 12"
return str::clean($str, 'aA1s');

#: Test All and Costume
#: Use String
#: Expect String "Hello World 12!!"
return str::clean($str, 'aA1s', '!');

#: Test All and Costume Extra
#: Use String
#: Expect String "Hello World (12)!!"
return str::clean($str, 'aA1s', '!()');

#: Test Exception on Invalid Mask
#: Expect Exception mysli\toolkit\exception\str 10
return str::clean('Foo', '___');
