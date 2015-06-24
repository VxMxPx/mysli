--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$data = [
    'name'       => 'Slovenia',
    'capital'    => 'Ljubljana',
    'area'       => 20273,
    'population' => 2061085,
    'hdi'        => 0.874
];
var_dump(arr::get($data, 'name'));
var_dump(arr::get($data, 'language'));
var_dump(arr::get($data, 'language', 'Slovene'));
?>
--EXPECT--
string(8) "Slovenia"
NULL
string(7) "Slovene"
