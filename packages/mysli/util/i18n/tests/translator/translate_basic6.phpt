--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate(['NUMBERS', 7]));
echo "\n";
print_r($t->translate(['NUMBERS', 107]));
echo "\n";
print_r($t->translate(['NUMBERS', 4]));
echo "\n";
print_r($t->translate(['NUMBERS', 400]));
echo "\n";
print_r($t->translate(['NUMBERS', 407]));
echo "\n";
print_r($t->translate(['NUMBERS', 10002]));
echo "\n";
print_r($t->translate(['NUMBERS', 12]));
?>
--EXPECT--
I'm ending with 7!
I'm ending with 7!
I'm starting with 4!
I'm starting with 4!
I'm ending with 7!
I'm starting with 1 and ending with 2!
I'm starting with 1 and ending with 2!
