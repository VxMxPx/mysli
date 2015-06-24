--TEST--
Check if non-existant language exists.
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

var_dump($t->exists('si'));
?>
--EXPECT--
int(0)
