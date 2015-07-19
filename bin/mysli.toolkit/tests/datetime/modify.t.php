<?php

#: Before
use mysli\toolkit\datetime;

#: Define Instance
$datetime = new datetime("2003-12-24 22:00:10", "UTC");

#: Test Modify
#: Use Instance
#: Expect String "1072389610"
return $datetime->modify("+1 Day");
