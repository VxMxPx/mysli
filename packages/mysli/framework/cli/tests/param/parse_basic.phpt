--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

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
