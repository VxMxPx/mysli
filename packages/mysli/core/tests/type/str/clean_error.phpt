--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

str::clean('21', '');

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message %a
