--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->command = 'test';
$params->description = 'Testing package!';
$params->description_long = 'This is a looong description! Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
$params->add(
    '--long/-l',
    ['type'    => 'str',
     'help'    => 'Long and short parameter']);
$params->add(
    '-s',
    ['type'    => 'str',
     'default' => 'def',
     'help'    => 'Only short parameter']);
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
  -s            [def]  Only short parameter
      --noshort        Only long parameter

This is a looong description! Lorem ipsum dolor sit amet, consectetur
adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore
magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
officia deserunt mollit anim id est laborum.
