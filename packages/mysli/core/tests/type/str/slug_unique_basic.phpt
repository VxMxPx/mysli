--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

var_dump(str::slug_unique('Hello World!!', ['hello-world']));
var_dump(str::slug_unique('Hello World!!', ['hello-world', 'hello-world-2']));
var_dump(str::slug_unique('Hello World!!', ['hello-world-2']));

?>
--EXPECT--
string(13) "hello-world-2"
string(13) "hello-world-3"
string(11) "hello-world"
