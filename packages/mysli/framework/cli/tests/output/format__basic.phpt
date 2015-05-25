--TEST--
--FILE--
<?php
use mysli\framework\cli\output as cout;

cout::format('Hello +bold+red%s-bold-red', ['World']) . "\n";
cout::format('Hello +bold+red%s-all', ['World']) . "\n";

?>
--EXPECT--
Hello [1m[31mWorld[21m[39m[0m
Hello [1m[31mWorld[0m[0m
