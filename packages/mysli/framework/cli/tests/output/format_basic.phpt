--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\cli\output as cout;

echo cout::format('Hello +bold+red%s-bold-red', ['World']) . "\n";
echo cout::format('Hello +bold+red%s-all', ['World']) . "\n";

?>
--EXPECTF--
Hello \e[1m\e[31mWorld\e[21m\e[39m
Hello \e[1m\e[31mWorld\e[0m
