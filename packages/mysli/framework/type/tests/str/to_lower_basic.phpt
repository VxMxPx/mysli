--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(str::to_lower('ŠĐŽČĆŽ'));

?>
--EXPECTF--
string(%d) "šđžčćž"
