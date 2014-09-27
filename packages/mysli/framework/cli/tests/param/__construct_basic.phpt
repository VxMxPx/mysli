--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$param = new cparam();

var_dump($param instanceof cparam);

?>
--EXPECT--
bool(true)
