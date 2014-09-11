--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::limit_repeat('Hello world!!!', '!', 1));
var_dump(str::limit_repeat('Hello world!!!??????', ['!', '?'], 2));

?>
--EXPECT--
string(12) "Hello world!"
string(15) "Hello world!!??"
