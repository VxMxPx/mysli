--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->command = 'test';
$params->description = 'Testing package!';
$params->add(
    '--a-very-very-very-very-very-very-very-very-very-very-long-parameter/-l',
    ['type'    => 'str',
     'help'    => 'Long and short parameter, with a very, very, very, very long description.']);
$params->add(
    '-s',
    ['type'    => 'str',
     'default' => 'def',
     'help'    => 'Only short parameter, with a very, very long description.']);
$params->add(
    '--noshort',
    ['type'    => 'str',
     'help'    => 'Only long parameter, with a very, very long description.']);
$params->add(
    'POSITIONAL',
    ['type'    => 'str',
     'help'    => 'Positional parameter, with a very, very long description.']);

echo $params->help();

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Long argument cannot be longer than 40 characters: `a-very-very-very-very-very-very-very-very-very-very-long-parameter` for `a-very-very-very-very-very-very-very-very-very-very-long-parameter`.' in %a
