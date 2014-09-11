--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr as arr;
var_dump(arr::implode_keys([40, 50], ':'));
var_dump(arr::implode_keys(['name' => null, 'age' => null], '.'));
var_dump(arr::implode_keys([], '.'));
?>
--EXPECT--
string(3) "0:1"
string(8) "name.age"
string(0) ""
