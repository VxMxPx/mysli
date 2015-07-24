<?php

#: Before
use mysli\toolkit\type\str;

#: Define String
$str = 'Hello World (12)!!';

#: Test Basic
#: Use String
#: Expect String "Hello World 12"
return str::clean_regex($str, '/[^a-z0-9\\ ]/i');
