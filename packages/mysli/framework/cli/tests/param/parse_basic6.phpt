--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['-l', 'World', '-xy', '-s']);
$params->add('--long/-l');
$params->add('-s' , ['type' => 'bool']);
$params->add('-x' , ['type' => 'bool']);
$params->add('-y' , ['type' => 'bool', 'invert' => true]);

$params->parse();
print_r($params->messages());
echo "\n";
print_r($params->values());

?>
--EXPECTF--
Array
(
    [long] => World
    [x] => 1
    [y] =>%s
    [s] => 1
)
