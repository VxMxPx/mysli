--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

$params = new cparam('Params Test', ['-l', 'Hello World!']);
$params->add('--long/-l');

$params->parse();
print_r($params->values());

?>
--EXPECT--
Array
(
    [long] => Hello World!
)
