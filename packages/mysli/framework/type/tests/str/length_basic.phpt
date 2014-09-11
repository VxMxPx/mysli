--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::length('ŠĐŽČĆšđžčć', 'UTF-8'));
var_dump(str::length('Hello World', 'UTF-8'));

?>
--EXPECT--
int(10)
int(11)
