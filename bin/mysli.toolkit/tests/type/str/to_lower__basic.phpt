--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::to_lower('ŠĐŽČĆŽ'));

?>
--EXPECTF--
string(%d) "šđžčćž"
