--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam(
                'Params Test',
                ['-a', 'One', 'Two', 'Three', 'Four', 'Five']);
$params->add(
    '-a', [
        'type' => 'arr',
        'min'  => 2,
        'max'  => 4
    ]
);

$params->parse();
print_r($params->messages());

?>
--EXPECT--
Unexpected argument: `Five`
