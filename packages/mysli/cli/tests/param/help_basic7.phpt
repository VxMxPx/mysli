--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', ['--help']);
$params->command = 'test';
$params->description = 'Testing package!';
$params->add(
    '--long/-l',
    ['type'     => 'str',
     'required' => true,
     'help'     => 'Long and short parameter']);
$params->add(
    'POSITIONAL',
    ['type'    => 'str',
     'help'    => 'Positional parameter']);

$params->add(
    'POSITIONAL_OPTIONAL',
    ['type'     => 'str',
     'required' => false,
     'help'     => 'Positional optional parameter']);

$params->parse();
var_dump($params->is_valid());
echo "\n";
print_r($params->messages());

?>
--EXPECT--
bool(false)

Params Test
Testing package!

Usage: ./dot test OPTIONS... POSITIONAL [POSITIONAL_OPTIONAL]

  POSITIONAL           Positional parameter
  POSITIONAL_OPTIONAL  Positional optional parameter

Options:
  -h, --help  Display this help
  -l, --long  Long and short parameter
