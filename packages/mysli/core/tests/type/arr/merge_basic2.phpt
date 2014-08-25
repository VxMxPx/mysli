--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$ar1 = array("color" => array("favorite" => "red"), 5);
$ar2 = array(10, "color" => array("favorite" => "green", "blue"));
print_r(arr::merge($ar1, $ar2));
print_r(arr::merge($ar1, $ar2, arr::merge_all));
?>
--EXPECT--
Array
(
    [color] => Array
        (
            [favorite] => green
            [0] => blue
        )

    [0] => 10
)
Array
(
    [color] => Array
        (
            [favorite] => green
            [0] => blue
        )

    [0] => 10
)
