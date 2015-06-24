--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', []);
$params->add(
    '-a', [
        'type' => 'arr',
        'min'  => 6,
        'max'  => 4
    ]
);

$params->parse();

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\argument' with message 'Values for `min` (6) cannot be bigger than value for `max` (4)' in %s
%a
