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

arr_path::set($array, 'main/settings/timezone', 'GMT+2');
var_dump($array['main']['settings']['timezone']);

arr_path::set($array, 'main/settings/debug', false);
var_dump($array['main']['settings']['debug']);
?>
--EXPECT--
string(5) "GMT+2"
bool(false)
