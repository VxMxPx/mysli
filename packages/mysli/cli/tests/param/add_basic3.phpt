--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '--test',
    ['action' => function () {}]);

var_dump($params->params());

?>
--EXPECT--
array(1) {
  ["test"]=>
  array(10) {
    ["id"]=>
    string(4) "test"
    ["short"]=>
    NULL
    ["long"]=>
    string(4) "test"
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
    object(Closure)#2 (0) {
    }
    ["invert"]=>
    bool(false)
  }
}
