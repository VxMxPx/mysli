<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect Integer 11
return str::length('Hello World', 'UTF-8');

#: Test UTF-8
#: Expect Integer 10
return str::length('ŠĐŽČĆšđžčć', 'UTF-8');
