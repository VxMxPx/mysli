<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "ŠĐŽČĆ"
return str::slice('ŠĐŽČĆ-šđžčć', 0, 5);

#: Test Negative
#: Expect String "šđžčć"
return str::slice('ŠĐŽČĆ-šđžčć', -5);

#: Test Middle, Negative
#: Expect String "ŽČĆ-šđž"
return str::slice('ŠĐŽČĆ-šđžčć', 2, -2);
