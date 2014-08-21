--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\base\arr as arr;
var_dump(arr::readable([]));
?>
--EXPECT--
string(0) ""
