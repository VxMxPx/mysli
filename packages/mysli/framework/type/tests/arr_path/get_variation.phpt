--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
$arr = [
    'pets' => [
        'bunny',
        'cat' => [
            'Riki'
        ],
        'dog'
    ]
];
var_dump(arr_path::get($arr, 'pets/0'));
var_dump(arr_path::get($arr, 'pets/cat/0'));
print_r(arr_path::get($arr, 'pets'));
var_dump(arr_path::get([], 'pets/cat/0'));
?>
--EXPECT--
string(5) "bunny"
string(4) "Riki"
Array
(
    [0] => bunny
    [cat] => Array
        (
            [0] => Riki
        )

    [1] => dog
)
NULL
