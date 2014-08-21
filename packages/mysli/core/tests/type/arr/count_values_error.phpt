--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\arr as arr;
$values = [
    ['Ljubljana', 'Maribor'],
    ['Triglav', 'Škrlatica', 'Mangart', 'Triglav'],
    ['Ljubljana', 'Maribor'],
    ['Maribor', 'Ljubljana']
];
print_r(arr::count_values($values, false));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\core\exception\argument' with message %a
