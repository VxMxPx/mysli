<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect Integer 13
return arr::last_key([12 => 'hello', 13 => 'world']);

#: Test Empty
#: Expect Null
return arr::last_key([]);
