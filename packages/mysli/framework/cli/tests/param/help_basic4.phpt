--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->command = 'test';
$params->description = 'Testing package!';
$params->add(
    '--a-very-very-very-very-very-long-argument/-l',
    ['type'    => 'str',
     'default' => 'a-very-very-very-very-very-long-default',
     'help'    => 'Long and short parameter, with a very, very, very, very long description.']);
$params->add(
    '-s',
    ['type'    => 'str',
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
Params Test
Testing package!

Usage: ./dot test [OPTIONS]... POSITIONAL

  POSITIONAL  Positional parameter, with a very, very long description.

Options:
  -h, --help%s
    Display this help
%s
  -l, --a-very-very-very-very-very-long-argument [a-very-very-v...]%s
    Long and short parameter, with a very, very, very, very long
    description.
%s
  -s%s
    Only short parameter, with a very, very long description.
%s
      --noshort%s
    Only long parameter, with a very, very long description.
