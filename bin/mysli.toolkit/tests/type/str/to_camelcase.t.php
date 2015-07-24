<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "HelloWorld"
return str::to_camelcase('hello_world');

#: Test One Word
#: Expect String "Hello"
return str::to_camelcase('hello');

#: Test Do Not Capitalize
#: Expect String "helloWorld"
return str::to_camelcase('hello_world', false);

#: Test Already Camelcased
#: Expect String "HelloWorld"
return str::to_camelcase('HelloWorld');

#: Test All-Upper
#: Expect String "HELLOWORLD"
return str::to_camelcase('HELLOWORLD');

#: Test Long Divider
#: Expect String "HelloWorld"
return str::to_camelcase('hello___world');

#: Test Start With Divider
#: Expect String "HelloWorld"
return str::to_camelcase('_hello_world');
