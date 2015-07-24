<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "šđžčćž"
return str::to_lower('ŠĐŽČĆŽ');
