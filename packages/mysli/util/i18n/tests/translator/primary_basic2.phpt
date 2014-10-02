--TEST--
Set/get primary language for translations.
--FILE--
<?php
use mysli\util\i18n\translator;

$t = new translator([
    'us' => [
        '.meta' => [
            'created_on' => 20140930,
            'modified'   => 20140930
        ],
        'HELLO_WORLD' => [
            'value' => 'Hello World!'
        ]
    ]], 'si', 'us');

print_r($t->primary());
$t->primary('us');
echo "\n";
print_r($t->primary());
?>
--EXPECT--
si
us
