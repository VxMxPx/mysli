--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$values = [
    ['Ljubljana', 'Maribor'],
    ['Triglav', 'Škrlatica', 'Mangart', 'Triglav'],
    ['Ljubljana', 'Maribor'],
    ['Maribor', 'Ljubljana']
];
print_r(arr::count_values($values, false));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message %a
