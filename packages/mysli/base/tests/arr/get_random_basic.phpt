--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
$data = ['a', 'b', 'c', 'd'];
var_dump(count(arr::get_random($data)));
?>
--EXPECT--
int(1)
