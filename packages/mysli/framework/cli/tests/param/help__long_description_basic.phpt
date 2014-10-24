--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->command = 'test';
$params->description = 'Testing package!';
$params->add(
    '--long/-l',
    ['type'    => 'str',
     'help'    => 'Long and short parameter']);
$params->add(
    '-s',
    ['type'    => 'str',
     'default' => 'def',
     'help'    => 'Only short parameter, with a very, very long description.']);
$params->add(
    '--noshort',
    ['type'    => 'str',
     'help'    => 'Only long parameter']);
$params->add(
    'POSITIONAL',
    ['type'    => 'str',
     'help'    => 'Positional parameter']);

echo $params->help();

?>
--EXPECT--
Params Test
Testing package!

Usage: ./dot test [OPTIONS]... POSITIONAL

  POSITIONAL  Positional parameter

Options:
  -h, --help           Display this help
  -l, --long           Long and short parameter
  -s            [def]  Only short parameter, with a very, very long
                       description.
      --noshort        Only long parameter
