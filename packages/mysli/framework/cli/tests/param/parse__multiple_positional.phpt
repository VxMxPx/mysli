--TEST--
--FILE--
<?php
use mysli\framework\cli\param as cparam;

$params = new cparam('Params Test', ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight']);
$params->add('ONE',   ['type' => 'str']);
$params->add('TWO',   ['type' => 'str']);
$params->add('THREE', ['type' => 'str']);
$params->add('FOUR',  ['type' => 'str']);
$params->add('FIVE',  ['type' => 'str']);
$params->add('SIX',   ['type' => 'str']);
$params->add('SEVEN', ['type' => 'str']);
$params->add('EIGHT', ['type' => 'str']);

$params->parse();
print_r($params->messages());
print_r($params->values());

?>
--EXPECT--
Array
(
    [one] => one
    [two] => two
    [three] => three
    [four] => four
    [five] => five
    [six] => six
    [seven] => seven
    [eight] => eight
)

