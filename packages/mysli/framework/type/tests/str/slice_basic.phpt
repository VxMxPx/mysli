--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::slice('ŠĐŽČĆ-šđžčć', 0, 5));
var_dump(str::slice('ŠĐŽČĆ-šđžčć', -5));
var_dump(str::slice('ŠĐŽČĆ-šđžčć', 2, -2));

?>
--EXPECTF--
string(%d) "ŠĐŽČĆ"
string(%d) "šđžčć"
string(%d) "ŽČĆ-šđž"
