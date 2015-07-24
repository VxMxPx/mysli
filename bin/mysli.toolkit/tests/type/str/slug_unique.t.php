<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "hello-world-2"
return str::slug_unique('Hello World!!', ['hello-world']);

#: Test Second Taken Too
#: Expect String "hello-world-3"
return str::slug_unique('Hello World!!', ['hello-world', 'hello-world-2']);

#: Test Second Taken, but First Available
#: Expect String "hello-world"
return str::slug_unique('Hello World!!', ['hello-world-2']);
