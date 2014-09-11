--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::split_get('cat dog bunny', ' ', 2));
var_dump(str::split_get('cat dog bunny', ' ', 3));
var_dump(str::split_get('cat dog bunny', ' ', 3, 'rabbit'));
var_dump(str::split_get(' cat,  dog,   bunny ,  ,     ', ',', 2, null, ' '));
var_dump(str::split_get('cat,dog,bunny', ',', 1, null, null, 2));

?>
--EXPECTF--
string(%d) "bunny"
NULL
string(%d) "rabbit"
string(%d) "bunny"
string(%d) "dog,bunny"
