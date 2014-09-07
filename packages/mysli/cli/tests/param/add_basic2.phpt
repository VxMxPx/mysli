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

var_dump($params->params());

?>
--EXPECT--
array(4) {
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
    bool(true)
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
    int(42)
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
    float(12.2)
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
  ["type_string"]=>
  array(10) {
    ["id"]=>
    string(11) "type_string"
    ["short"]=>
    NULL
    ["long"]=>
    string(11) "type_string"
    ["type"]=>
    string(3) "str"
    ["default"]=>
    string(11) "hello world"
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
}
