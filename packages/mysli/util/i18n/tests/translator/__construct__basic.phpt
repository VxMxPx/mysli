--TEST--
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
print_r($t);

?>
--EXPECT--
mysli\util\i18n\translator Object
(
    [primary:protected] => si
    [secondary:protected] => us
    [dictionary:protected] => Array
        (
            [us] => Array
                (
                    [.meta] => Array
                        (
                            [created_on] => 20140930
                            [modified] => 20140930
                        )

                    [HELLO_WORLD] => Array
                        (
                            [value] => Hello World!
                        )

                )

        )

)
