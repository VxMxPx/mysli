--TEST--
Translate the key, basic!
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate(
            'GREETING_AND_REGISTER', [
                '<a href="#login">%s</a>',
                '<a href="#register">%s</a>']));
?>
--EXPECT--
Hi there, please <a href="#login">login</a> or <a href="#register">register</a>.
