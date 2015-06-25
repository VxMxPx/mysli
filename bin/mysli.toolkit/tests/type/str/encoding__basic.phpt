--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::encoding() === mb_internal_encoding());

str::encoding('UTF-16');
var_dump(mb_internal_encoding());
?>
--EXPECT--
bool(true)
string(6) "UTF-16"