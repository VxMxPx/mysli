--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

var_dump(str::limit_length('Lorem ipsum dolor sit amet consectetur.', 15));
var_dump(str::limit_length('Lorem ipsum dolor sit amet consectetur.', 15, '...'));
var_dump(str::limit_length('Lorem ipsum dolor.', 30, '...'));

?>
--EXPECTF--
string(%d) "Lorem ipsum dol"
string(%d) "Lorem ipsum dol..."
string(%d) "Lorem ipsum dolor."
