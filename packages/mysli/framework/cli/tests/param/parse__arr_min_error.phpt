--TEST--
--FILE--
<?php
use mysli\util\datetime as dtime;
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['-a', 'One']);
$params->add(
    '-a', [
        'type' => 'arr',
        'min'  => 2,
        'max'  => 2
    ]
);

$params->parse();
print_r($params->messages());

?>
--EXPECT--
Expected at least `2` arguments for `a`.
