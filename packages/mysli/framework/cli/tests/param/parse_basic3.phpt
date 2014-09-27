--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['-l', 'Hello', 'World']);
$params->add(
    '--long/-l', [
        'required' => true
    ]
);
$params->add('POSITIONAL');

$params->parse();
print_r($params->messages());
print_r($params->values());

?>
--EXPECT--
Array
(
    [long] => Hello
    [positional] => World
)
