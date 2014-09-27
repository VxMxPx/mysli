--TEST--
--FILE--
<?php
print_r([
    'one'   => 1,
    'two'   => 2,
    'three' => 3,
    'four'  => 4
]);
?>
--EXPECTF--
Array
(
    [one] => %d
    [two] => %d
    [three] => %d
    [four] => %d
)
