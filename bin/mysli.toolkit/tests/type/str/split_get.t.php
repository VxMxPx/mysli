<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "bunny"
return str::split_get('cat dog bunny', ' ', 2);

#: Test No Value
#: Expect Null
return str::split_get('cat dog bunny', ' ', 3);

#: Test Defualt
#: Expect String "rabbit"
return str::split_get('cat dog bunny', ' ', 3, 'rabbit');

#: Test Trim
#: Expect String "bunny"
return str::split_get(' cat,  dog,   bunny ,  ,     ', ',', 2, null, ' ');

#: Test Limit
#: Expect String "dog,bunny"
return str::split_get('cat,dog,bunny', ',', 1, null, null, 2);
