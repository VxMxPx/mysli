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
var_dump(arr_path::get($array, 'main/settings/debug'));
var_dump(arr_path::get($array, 'main/settings/timezone'));
print_r(arr_path::get($array, 'main/settings'));
var_dump(arr_path::get($array, 'main/settings/not_there', 'Default Value'));
?>
--EXPECT--
bool(true)
string(3) "GMT"
Array
(
    [timezone] => GMT
    [debug] => 1
)
string(13) "Default Value"
