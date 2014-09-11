--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\output as cout;

echo cout::format('Hello +bold+red%s-bold-red', ['World']) . "\n";
echo cout::format('Hello +bold+red%s-all', ['World']) . "\n";

?>
--EXPECTF--
Hello \e[21m\e[39mWorld\e[21m\e[39m
Hello \e[21m\e[39mWorld\e[0m
