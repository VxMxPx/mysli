--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate('MULTILINE_KEEP_LINES'));

?>
--EXPECT--
Hello,
the text will stay
in multiple lines!
