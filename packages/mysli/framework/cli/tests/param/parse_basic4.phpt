--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['World']);
$params->add(
    '--long/-l', [
        'required' => true
    ]
);
$params->add('POSITIONAL');

$params->parse();
print_r($params->messages());
echo "\n";
print_r($params->values());

?>
--EXPECT--
Missing parameter: `long`.
Array
(
    [positional] => World
)
