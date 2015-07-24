<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "SDZCC-sdzcc"
return str::normalize('ŠĐŽČĆ-šđžčć');
