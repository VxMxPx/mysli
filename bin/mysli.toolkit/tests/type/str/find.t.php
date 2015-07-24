<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect Integer 1
return str::find('abcd', 'b');

#: Test Non Existant
#: Expect False
return str::find('abcd', 'f');

#: Test Encoding
#: Expect Integer 6
return str::find('šđžčć šđžčć', 'š', 1, 'UTF-8');
