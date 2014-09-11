--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::limit_words('Lorem ipsum dolor sit amet consectetur.', 3));
var_dump(str::limit_words('Lorem ipsum dolor sit amet consectetur.', 3, '...'));
var_dump(str::limit_words('Lorem ipsum dolor.', 3, '...'));

?>
--EXPECTF--
string(%d) "Lorem ipsum dolor"
string(%d) "Lorem ipsum dolor..."
string(%d) "Lorem ipsum dolor."
