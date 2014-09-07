--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$param = new cparam();

var_dump($param instanceof cparam);

?>
--EXPECT--
bool(true)
