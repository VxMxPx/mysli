--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

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
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [action] => Closure Object
                (
                )

            [invert] =>%s
            [ignore] =>%s
        )

)
