--TEST--
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::encode([
    'name'    => 'bar',
    'require' => [],
    'line'    => 'foo'
]));
?>
--EXPECTF--
name: bar
require: []
line: foo
