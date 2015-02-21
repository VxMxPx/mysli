--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['-a', 'One', 'Two', 'Three']);
$params->add(
    '-a', [
        'type' => 'arr',
        'min'  => 2,
        'max'  => 2
    ]
);
$params->add('POSITIONAL');

$params->parse();
print_r($params->values());

?>
--EXPECT--
Array
(
    [a] => Array
        (
            [0] => One
            [1] => Two
        )

    [positional] => Three
)
