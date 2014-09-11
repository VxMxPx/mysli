--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(mb_internal_encoding() === str::encoding());

str::encoding('UTF-16');
var_dump(mb_internal_encoding());

?>
--EXPECT--
bool(true)
string(6) "UTF-16"
