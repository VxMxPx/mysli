<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
#: Expect String "world"
return arr::last(['hello', 'world']);

#: Test Empty
#: Expect Null
return arr::last([]);
