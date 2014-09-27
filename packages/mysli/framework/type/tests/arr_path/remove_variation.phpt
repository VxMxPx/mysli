--TEST--
--FILE--
<?php

use mysli\framework\type\arr_path as arr_path;
$array = [
    'main' => [
        'settings' => [
            'timezone' => 'GMT'
        ]
    ]
];

arr_path::remove($array, '');
print_r($array);

arr_path::remove($array, 'main/not_found');
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
                )

        )

)
Array
(
    [main] => Array
        (
            [settings] => Array
                (
                    [timezone] => GMT
                )

        )

)
