--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::to_upper('šđžčćž'));

?>
--EXPECTF--
string(%d) "ŠĐŽČĆŽ"
