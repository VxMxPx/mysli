<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect Integer 12
return arr::first_key([12 => 'hello', 13 => 'world']);

#: Test Empty
#: Expect Null
return arr::first_key([]);
