--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::normalize('ŠĐŽČĆ-šđžčć'));
?>
--EXPECT--
string(11) "SDZCC-sdzcc"
