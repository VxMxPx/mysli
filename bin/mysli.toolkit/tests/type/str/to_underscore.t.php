<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "hello_world"
return str::to_underscore('HelloWorld');

#: Test Already Underscored
#: Expect String "hello_world"
return str::to_underscore('hello_world');

#: Test Long Divider
#: Expect String "hello__world"
return str::to_underscore('hello__World');

#: Test All-Upper
#: Expect String "helloworld"
return str::to_underscore('HELLOWORLD');

#: Test First Word Upper
#: Expect String "helloworld"
return str::to_underscore('HELLOWorld');

#: Test Second Word Upper
#: Expect String "hello_world"
return str::to_underscore('HelloWORLD');
