--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '--type_bool',
    ['type' => 'bool',
    'default' => true]);
$params->add(
    '--type_int',
    ['type' => 'int',
    'default' => 42]);
$params->add(
    '--type_float',
    ['type' => 'float',
    'default' => 12.2]);
$params->add(
    '--type_string',
    ['default' => 'hello world']);

print_r(array_slice($params->dump()[0], 1));

?>
--EXPECTF--
Array
(
    [type_bool] => Array
        (
            [id] => type_bool
            [short] =>%s
            [long] => type_bool
            [type] => bool
            [default] => 1
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [type_int] => Array
        (
            [id] => type_int
            [short] =>%s
            [long] => type_int
            [type] => int
            [default] => 42
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [type_float] => Array
        (
            [id] => type_float
            [short] =>%s
            [long] => type_float
            [type] => float
            [default] => 12.2
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [type_string] => Array
        (
            [id] => type_string
            [short] =>%s
            [long] => type_string
            [type] => str
            [default] => hello world
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

)
