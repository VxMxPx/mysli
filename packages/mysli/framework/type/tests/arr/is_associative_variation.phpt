--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr as arr;
var_dump(arr::is_associative([]));
var_dump(arr::is_associative([[]]));
var_dump(arr::is_associative(['b' => []]));
var_dump(arr::is_associative([2 => 'two']));
var_dump(arr::is_associative(['2' => 'two']));
var_dump(arr::is_associative([
    ['name' => 2, 'age' => 3],
    ['name' => 2, 'age' => 3],
    ['name' => 2, 'age' => 3],
    ['name' => 2, 'age' => 3]
]));
?>
--EXPECT--
bool(false)
bool(false)
bool(true)
bool(false)
bool(false)
bool(false)
