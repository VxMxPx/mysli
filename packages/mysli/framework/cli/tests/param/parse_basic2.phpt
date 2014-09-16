--TEST--
--FILE--
<?php
include __DIR__.'/../_common.php';
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
Params Test

Usage: ./dot COMMAND OPTIONS...


Options:
  -h, --help  Display this help
  -l, --long
