--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
var_dump(arr::key_in(['hi'], 0));
var_dump(arr::key_in(['greetings' => 'hi'], 'greetings'));
var_dump(arr::key_in(['greetings' => 'hi'], -1));
var_dump(arr::key_in(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'mail']));
var_dump(arr::key_in(
    ['name' => null, 'age' => 22, 'mail' => 0],
    ['name', 'address']));
?>
--EXPECT--
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
