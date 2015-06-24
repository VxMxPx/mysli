--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT',
            'debug'    => true
        ]
    ]
];

arr_path::set($array, 'number', 42);
print_r($array);

arr_path::set($array, '', 43);
print_r($array);
?>
--EXPECT--
Array
(
    [main] => Array
        (
            [settings] => Array
                (
                    [timezone] => GMT
                    [debug] => 1
                )

        )

    [number] => 42
)
Array
(
    [main] => Array
        (
            [settings] => Array
                (
                    [timezone] => GMT
                    [debug] => 1
                )

        )

    [number] => 42
    [] => 43
)
