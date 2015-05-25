--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate(['AGE', 0]));
echo "\n";
print_r($t->translate(['AGE', 1]));
echo "\n";
print_r($t->translate(['AGE', 2]));
echo "\n";
print_r($t->translate(['AGE', 4]));
echo "\n";
print_r($t->translate(['AGE', 8]));
echo "\n";
print_r($t->translate(['AGE', 15]));
echo "\n";
print_r($t->translate(['AGE', 22]));
echo "\n";
print_r($t->translate(['AGE', 54]));
echo "\n";
print_r($t->translate(['AGE', 65]));
echo "\n";
print_r($t->translate(['AGE', 90]));
echo "\n";
?>
--EXPECT--
Hopes
Hopes
Will
Purpose
Competence
Fidelity
Love
Care
Wisdom
Wisdom
