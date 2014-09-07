--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

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
Missing required parameter: `long`.
