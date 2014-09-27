--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
$array = [
    'pets' => [
        'dog', 'cat', 'bunny'
    ]
];
arr_path::set($array, 'pets', ['turtle', 'piglet']);
print_r($array);

arr_path::set($array, 'pets/2', 'rabbit');
print_r($array);

arr_path::set($array, 'pets/2', ['fluffy', 'bunny']);
print_r($array);
?>
--EXPECT--
Array
(
    [pets] => Array
        (
            [0] => turtle
            [1] => piglet
            [2] => bunny
        )

)
Array
(
    [pets] => Array
        (
            [0] => turtle
            [1] => piglet
            [2] => rabbit
        )

)
Array
(
    [pets] => Array
        (
            [0] => turtle
            [1] => piglet
            [2] => Array
                (
                    [0] => fluffy
                    [1] => bunny
                )

        )

)
