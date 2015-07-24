<?php

#: Before
use mysli\toolkit\type\str;

#: Test Set Encoding UTF-16
#: Expect String "UTF-16"
$o = mb_internal_encoding();
str::encoding('UTF-16');
$r = mb_internal_encoding();
str::encoding($o);
return $r;
