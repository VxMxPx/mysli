--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate(['ODD', 7]));
echo "\n";
print_r($t->translate(['ODD', 103]));
echo "\n";
print_r($t->translate(['ODD', 4]));
echo "\n";
print_r($t->translate(['ODD', 400]));
echo "\n";
print_r($t->translate(['ODD', 405]));
echo "\n";
print_r($t->translate(['ODD', 10002]));
echo "\n";
print_r($t->translate(['ODD', 1]));
echo "\n";
print_r($t->translate(['ODD', 4566]));
echo "\n";
print_r($t->translate(['ODD', 48]));
echo "\n";
print_r($t->translate(['ODD', 459]));

?>
--EXPECT--
I'm odd! :S
I'm odd! :S
I'm even! :)
I'm even! :)
I'm odd! :S
I'm even! :)
I'm odd! :S
I'm even! :)
I'm even! :)
I'm odd! :S
