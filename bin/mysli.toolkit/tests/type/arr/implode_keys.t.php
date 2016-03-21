<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Associative
#: Expect String "name.age"
return arr::implode_keys(['name' => null, 'age' => null], '.');

#: Test Not Associative
#: Expect String "0:1"
return arr::implode_keys([40, 50], ':');

#: Test Empty
#: Expect String ""
return arr::implode_keys([], '.');
