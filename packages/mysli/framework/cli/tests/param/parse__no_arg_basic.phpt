--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '--long/-l', [
        'required' => true
    ]
);

$params->parse();
print_r($params->messages());

?>
--EXPECT--
Missing parameter: `long`