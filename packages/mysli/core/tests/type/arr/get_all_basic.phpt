--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$data = [
    'name'       => 'Slovenia',
    'capital'    => 'Ljubljana',
    'area'       => 20273,
    'population' => 2061085,
    'hdi'        => 0.874
];
print_r(arr::get_all($data, 'name'));
print_r(arr::get_all($data, 'name,capital'));
print_r(arr::get_all($data, ['name', 'area', 'population']));
var_dump(arr::get_all($data, 'language'));
print_r(arr::get_all($data, 'language', 'Slovene'));
print_r(arr::get_all(
    $data, 'language,calling_code,tld', ['Slovene', '+386', '.si']));
var_dump(arr::get_all(
    $data, 'language,calling_code,tld', ['Slovene']));
print_r(arr::get_all(
    $data, 'language,calling_code,tld', 'N/A'));
?>
--EXPECT--
Array
(
    [0] => Slovenia
)
Array
(
    [0] => Slovenia
    [1] => Ljubljana
)
Array
(
    [0] => Slovenia
    [1] => 20273
    [2] => 2061085
)
array(1) {
  [0]=>
  NULL
}
Array
(
    [0] => Slovene
)
Array
(
    [0] => Slovene
    [1] => +386
    [2] => .si
)
array(3) {
  [0]=>
  string(7) "Slovene"
  [1]=>
  NULL
  [2]=>
  NULL
}
Array
(
    [0] => N/A
    [1] => N/A
    [2] => N/A
)
