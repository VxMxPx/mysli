<?php

#: Before
use mysli\toolkit\datetime;

#: Define Instance
$datetime = new datetime("2003-12-24 22:00:10", "UTC");

#: Test Diff Days
#: Use Instance
#: Expect Integer 1
return $datetime->diff("2003-12-25 22:00:10")->days;
