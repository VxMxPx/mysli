--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::readable([]));
?>
--EXPECT--
string(0) ""
