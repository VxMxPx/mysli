--TEST--
--FILE--
<?php
include __DIR__.'/../../_common.php';
use mysli\core\type\str as str;

print_r(
    str::tokenize(
        'class="primary button big" id="register" value="Register"', ' ', '"'));

print_r(
    str::tokenize(
        '(group 1) (group 2) (group 3)', ' ', ['(', ')']));

?>
--EXPECT--
Array
(
    [0] => class="primary button big"
    [1] => id="register"
    [2] => value="Register"
)
Array
(
    [0] => (group 1)
    [1] => (group 2)
    [2] => (group 3)
)
