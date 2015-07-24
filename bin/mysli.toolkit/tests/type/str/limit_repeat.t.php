<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "Hello world!"
return str::limit_repeat('Hello world!!!', '!', 1);

#: Test Two Characters
#: Expect String "Hello world!!??"
return str::limit_repeat('Hello world!!!??????', ['!', '?'], 2);
