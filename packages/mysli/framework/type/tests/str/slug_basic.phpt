--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::slug('Hello World (12)!!'));
var_dump(str::slug('hello---world'));
var_dump(str::slug('Hello World (12)!!', '_'));
var_dump(str::slug('Hello World (12)!!', '__'));

?>
--EXPECT--
string(14) "hello-world-12"
string(11) "hello-world"
string(14) "hello_world_12"
string(16) "hello__world__12"
