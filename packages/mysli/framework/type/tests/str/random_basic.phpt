--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\str as str;

var_dump(preg_match('/[a-z]{10}/', str::random(10, 'a')));
var_dump(preg_match('/[a-z]{10}/i', str::random(10, 'aA')));
var_dump(preg_match('/[a-z0-9]{10}/i', str::random(10, 'aA1')));
var_dump(
    preg_match(
        '/[a-z0-9'. preg_quote('~#$%&()=?*<>-_:.;,+!') .']{10}/i',
        str::random(10, 'aA1s')));

?>
--EXPECT--
int(1)
int(1)
int(1)
int(1)
