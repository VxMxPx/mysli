--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::to_camelcase('hello_world'));
var_dump(str::to_camelcase('hello'));
var_dump(str::to_camelcase('hello_world', false));
var_dump(str::to_camelcase('HelloWorld'));
var_dump(str::to_camelcase('HELLOWORLD'));
var_dump(str::to_camelcase('hello___world'));
var_dump(str::to_camelcase('_hello_world'));

?>
--EXPECTF--
string(%d) "HelloWorld"
string(%d) "Hello"
string(%d) "helloWorld"
string(%d) "HelloWorld"
string(%d) "HELLOWORLD"
string(%d) "HelloWorld"
string(%d) "HelloWorld"
