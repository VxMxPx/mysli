--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr_path as arr_path;
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT',
            'debug'    => true
        ]
    ]
];

arr_path::set($array, 'main/settings', ['timezone' => 'GMT+2', 'debug' => 12]);
print_r($array);
?>
--EXPECT--
Array
(
    [main] => Array
        (
            [settings] => Array
                (
                    [timezone] => GMT+2
                    [debug] => 12
                )

        )

)
