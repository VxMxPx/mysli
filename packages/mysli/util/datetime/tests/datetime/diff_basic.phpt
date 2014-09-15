--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\datetime as dtime;

$dt = new dtime('2014-04-01 12:10:20', 'UTC');

var_dump($dt->diff('2014-04-02 16:10:20')->days);

?>
--EXPECT--
int(1)