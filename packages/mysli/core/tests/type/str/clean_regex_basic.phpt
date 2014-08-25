--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

$str = 'Hello World (12)!!';

var_dump(str::clean_regex($str, '/[^a-z0-9\\ ]/i'));

?>
--EXPECT--
string(14) "Hello World 12"
