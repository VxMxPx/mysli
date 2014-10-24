--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;

$dt = new dtime('2014-04-01 12:10:20', 'UTC');

var_dump($dt->diff('2014-04-02 16:10:20')->days);

?>
--EXPECT--
int(1)
