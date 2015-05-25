--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate(['TWO_AND_NINE', 2]));
echo "\n";
print_r($t->translate(['TWO_AND_NINE', 9]));
echo "\n";
var_dump($t->translate(['TWO_AND_NINE', 34]));
var_dump($t->translate(['TWO_AND_NINE', 3242]));
var_dump($t->translate(['TWO_AND_NINE', 3249]));
?>
--EXPECT--
Two or nine!
Two or nine!
NULL
NULL
NULL
