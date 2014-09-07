--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

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

var_dump($params->params());

?>
--EXPECT--
array(9) {
  ["long"]=>
  array(10) {
    ["id"]=>
    string(4) "long"
    ["short"]=>
    string(1) "l"
    ["long"]=>
    string(4) "long"
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    string(25) "Long and short parameter."
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["s"]=>
  array(10) {
    ["id"]=>
    string(1) "s"
    ["short"]=>
    string(1) "s"
    ["long"]=>
    NULL
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    string(21) "Only short parameter."
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["noshort"]=>
  array(10) {
    ["id"]=>
    string(7) "noshort"
    ["short"]=>
    NULL
    ["long"]=>
    string(7) "noshort"
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    string(20) "Only long parameter."
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["type_bool"]=>
  array(10) {
    ["id"]=>
    string(9) "type_bool"
    ["short"]=>
    NULL
    ["long"]=>
    string(9) "type_bool"
    ["type"]=>
    string(4) "bool"
    ["default"]=>
    NULL
    ["help"]=>
    NULL
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["type_int"]=>
  array(10) {
    ["id"]=>
    string(8) "type_int"
    ["short"]=>
    NULL
    ["long"]=>
    string(8) "type_int"
    ["type"]=>
    string(3) "int"
    ["default"]=>
    NULL
    ["help"]=>
    NULL
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["type_float"]=>
  array(10) {
    ["id"]=>
    string(10) "type_float"
    ["short"]=>
    NULL
    ["long"]=>
    string(10) "type_float"
    ["type"]=>
    string(5) "float"
    ["default"]=>
    NULL
    ["help"]=>
    NULL
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["required"]=>
  array(10) {
    ["id"]=>
    string(8) "required"
    ["short"]=>
    NULL
    ["long"]=>
    string(8) "required"
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    NULL
    ["required"]=>
    bool(true)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["specialid"]=>
  array(10) {
    ["id"]=>
    string(9) "specialid"
    ["short"]=>
    string(1) "d"
    ["long"]=>
    NULL
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    NULL
    ["required"]=>
    bool(false)
    ["positional"]=>
    bool(false)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
  ["positional"]=>
  array(10) {
    ["id"]=>
    string(10) "positional"
    ["short"]=>
    NULL
    ["long"]=>
    NULL
    ["type"]=>
    string(3) "str"
    ["default"]=>
    NULL
    ["help"]=>
    string(21) "Positional parameter."
    ["required"]=>
    bool(true)
    ["positional"]=>
    bool(true)
    ["action"]=>
    bool(false)
    ["invert"]=>
    bool(false)
  }
}
