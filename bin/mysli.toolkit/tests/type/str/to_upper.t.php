<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "ŠĐŽČĆŽ"
return str::to_upper('šđžčćž');
