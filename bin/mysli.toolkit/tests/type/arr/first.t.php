<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect String "hello"
return arr::first(['hello', 'world']);

#: Test Empty
#: Expect Null
return arr::first([]);
