--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

$str = 'Hello World (12)!!';

var_dump(str::clean($str, 'a'));
var_dump(str::clean($str, 'aA'));
var_dump(str::clean($str, 'aA1'));
var_dump(str::clean($str, 'aA1s'));
var_dump(str::clean($str, 'aA1s', '!'));
var_dump(str::clean($str, 'aA1s', '!()'));

?>
--EXPECTF--
string(%d) "elloorld"
string(%d) "HelloWorld"
string(%d) "HelloWorld12"
string(%d) "Hello World 12"
string(%d) "Hello World 12!!"
string(%d) "Hello World (12)!!"
