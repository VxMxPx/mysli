--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '--long/-l',
    ['type'    => 'str',
     'help'    => 'Long and short parameter.']);
$params->add(
    '-s',
    ['type'    => 'str',
     'help'    => 'Only short parameter.']);
$params->add(
    '--noshort',
    ['type'    => 'str',
     'help'    => 'Only long parameter.']);
$params->add(
    '--type_bool',
    ['type' => 'bool']);
$params->add(
    '--type_int',
    ['type' => 'int']);
$params->add(
    '--type_float',
    ['type' => 'float']);
$params->add(
    '--required',
    ['required' => true]);
$params->add(
    '-d',
    ['id' => 'specialid']);
$params->add(
    'POSITIONAL',
    ['type'    => 'str',
     'help'    => 'Positional parameter.']);

print_r(array_slice($params->dump()[0], 1));

?>
--EXPECTF--
Array
(
    [long] => Array
        (
            [id] => long
            [short] => l
            [long] => long
            [type] => str
            [default] =>%s
            [help] => Long and short parameter.
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [s] => Array
        (
            [id] => s
            [short] => s
            [long] =>%s
            [type] => str
            [default] =>%s
            [help] => Only short parameter.
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [noshort] => Array
        (
            [id] => noshort
            [short] =>%s
            [long] => noshort
            [type] => str
            [default] =>%s
            [help] => Only long parameter.
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [type_bool] => Array
        (
            [id] => type_bool
            [short] =>%s
            [long] => type_bool
            [type] => bool
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
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
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
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
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [required] => Array
        (
            [id] => required
            [short] =>%s
            [long] => required
            [type] => str
            [default] =>%s
            [help] =>%s
            [required] => 1
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [specialid] => Array
        (
            [id] => specialid
            [short] => d
            [long] =>%s
            [type] => str
            [default] =>%s
            [help] =>%s
            [required] =>%s
            [positional] =>%s
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

    [positional] => Array
        (
            [id] => positional
            [short] =>%s
            [long] =>%s
            [type] => str
            [default] =>%s
            [help] => Positional parameter.
            [required] => 1
            [positional] => 1
            [allow_empty] =>%s
            [exclude] =>%s
            [invoke] =>%s
            [action] =>%s
            [invert] =>%s
            [ignore] =>%s
        )

)
