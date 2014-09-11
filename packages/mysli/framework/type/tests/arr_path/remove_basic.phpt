--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr_path as arr_path;
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT',
            'debug'    => true
        ]
    ]
];

arr_path::remove($array, 'main/settings/timezone');
print_r($array);

arr_path::remove($array, 'main/settings');
print_r($array);
?>
--EXPECT--
Array
(
    [main] => Array
        (
            [settings] => Array
                (
                    [debug] => 1
                )

        )

)
Array
(
    [main] => Array
        (
        )

)
