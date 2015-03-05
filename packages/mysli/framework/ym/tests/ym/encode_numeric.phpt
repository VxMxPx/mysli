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
--EXPECT--
name: bar
require: []
line: foo
