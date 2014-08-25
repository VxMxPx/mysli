--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

var_dump(str::to_upper('šđžčćž'));

?>
--EXPECTF--
string(%d) "ŠĐŽČĆŽ"
