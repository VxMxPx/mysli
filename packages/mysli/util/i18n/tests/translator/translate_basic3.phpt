--TEST--
Translate the key, basic!
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate('GREETING_AND_AGE', ['Riki', 2]));
?>
--EXPECT--
Hi there, Riki you're 2 years old.
