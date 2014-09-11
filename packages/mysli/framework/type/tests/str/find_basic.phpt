--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::find('abcd', 'b'));
var_dump(str::find('abcd', 'c'));
var_dump(str::find('abcd', 'f'));
var_dump(str::find('šđžčć šđžčć', 'š', 1, 'UTF-8'));

?>
--EXPECT--
int(1)
int(2)
bool(false)
int(6)
