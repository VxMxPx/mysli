--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '--test',
    ['action' => function () {}]);

print_r(array_slice($params->dump()[0], 1));

?>
--EXPECTF--
Array
(
    [test] => Array
        (
            [id] => test
            [short] =>%s
            [long] => test
            [type] => str
            [min] =>%s
            [max] =>%s
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] => Closure Object
                (
                )

            [invert] =>%s
            [ignore] =>%s
        )

)
