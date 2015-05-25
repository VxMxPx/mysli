--TEST--
Complex example.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::encode([
    'package' => 'mysli.framework.ym',
    'version' => 1,
    'require' => false,
    'level1'  => [
        'item'   => 'Address',
        'level2' => [
            'item'   => 'Name',
            'level3' => [
                'item'   => 'Age',
                'level4' => false
            ],
            'item2'   => 'Surname',
            'level3a' => [
                'item' => true
            ]
        ]
    ]
]));
?>
--EXPECTF--
package: mysli.framework.ym
version: 1
require: No
level1:
    item: Address
    level2:
        item: Name
        level3:
            item: Age
            level4: No
        item2: Surname
        level3a:
            item: Yes

