--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::normalize('ŠĐŽČĆ-šđžčć'));
?>
--EXPECT--
string(11) "SDZCC-sdzcc"
