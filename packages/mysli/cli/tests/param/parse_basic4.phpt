--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\cli\param as cparam;

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
Missing required parameter: `long`.
Array
(
    [positional] => World
)
