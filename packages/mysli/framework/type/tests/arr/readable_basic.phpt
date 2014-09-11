--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\type\arr as arr;
$data = [
    'name'       => 'Slovenia',
    'capital'    => 'Ljubljana',
    'area'       => 20273,
    'population' => 2061085,
    'hdi'        => 0.874
];
echo arr::readable($data);
?>
--EXPECT--
name       : Slovenia
capital    : Ljubljana
area       : 20273
population : 2061085
hdi        : 0.874
