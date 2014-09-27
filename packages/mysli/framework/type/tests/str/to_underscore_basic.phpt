--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::to_underscore('HelloWorld'));
var_dump(str::to_underscore('hello_world'));
var_dump(str::to_underscore('hello__World'));
var_dump(str::to_underscore('HELLOWORLD'));
var_dump(str::to_underscore('HELLOWorld'));
var_dump(str::to_underscore('HelloWORLD'));

?>
--EXPECTF--
string(%d) "hello_world"
string(%d) "hello_world"
string(%d) "hello__world"
string(%d) "helloworld"
string(%d) "helloworld"
string(%d) "hello_world"
