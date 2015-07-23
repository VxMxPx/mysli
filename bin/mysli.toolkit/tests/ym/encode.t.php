<?php

#: Before
use mysli\toolkit\ym;

#: Test General
$encoded = ym::encode([
    'package' => 'mysli.toolkit',
    'version' => 1,
    'description' => 'Mysli Toolkit Core Library.',
    'license' => 'GPL-3.0',
    'authors' => [ "Marko Gajšt (Developer) <m@gaj.st>" ],
    'require' => false
]);
return assert::equals($encoded, <<<EXPECT
package: mysli.toolkit
version: 1
description: Mysli Toolkit Core Library.
license: GPL-3.0
authors:
    - Marko Gajšt (Developer) <m@gaj.st>
require: No

EXPECT
);

#: Test Boolean
$encoded = ym::encode([ true, false, 'Yes', 'No', 'True', 'False' ]);
return assert::equals($encoded, <<<EXPECT
- Yes
- No
- "Yes"
- "No"
- "Yes"
- "No"

EXPECT
);

#: Test Empty Array
$encoded = ym::encode([ 'name' => 'bar', 'require' => [], 'line' => 'foo' ]);
return assert::equals($encoded, <<<EXPECT
name: bar
require: []
line: foo

EXPECT
);

#: Test Deeply Nested
$encoded = ym::encode([
    'package' => 'mysli.toolkit',
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
]);
return assert::equals($encoded, <<<EXPECT
package: mysli.toolkit
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

EXPECT
);

#: Test No Key Array
$encoded = ym::encode([
    'receipt'  => 'Oz-Ware Purchase Invoice',
    'date'     => '2012-08-06',
    'customer' => [
        'given'  => 'Dorothy',
        'family' => 'Gale',
    ],
    'items' => [
        [
            'part_no'  => 'A4786',
            'descrip'  => 'Water Bucket (Filled)',
            'price'    => 1.47,
            'quantity' => 4,
        ],
        [
            'part_no'  => 'E1628',
            'descrip'  => 'High Heeled "Ruby" Slippers',
            'size'     => 8,
            'price'    => 100.27,
            'quantity' => 1,
        ]
    ]
]);
return assert::equals($encoded, <<<EXPECT
receipt: Oz-Ware Purchase Invoice
date: 2012-08-06
customer:
    given: Dorothy
    family: Gale
items:
    -
        part_no: A4786
        descrip: Water Bucket (Filled)
        price: 1.47
        quantity: 4
    -
        part_no: E1628
        descrip: High Heeled "Ruby" Slippers
        size: 8
        price: 100.27
        quantity: 1

EXPECT
);

#: Test Types
$encoded = ym::encode([
    's1' => 'I\'m string!',
    's2' => 'I\'m also a string',
    's3' => "113700",
    'i1' => 42,
    'i2' => -42,
    'f1' => 11.23,
    'f2' => -11.23,
    'b1' => true,
    'b2' => false,
    'b3' => true,
    'b4' => false,
    'a1' => [ 'An', 'array!' ],
    'a2' => [ 'associative' => 'array', 'key' => 'value' ],
    'a3' => [ 'mixed' => 'value', 'value', 'value2' ]
]);
return assert::equals($encoded, <<<EXPECT
s1: I'm string!
s2: I'm also a string
s3: "113700"
i1: 42
i2: -42
f1: 11.23
f2: -11.23
b1: Yes
b2: No
b3: Yes
b4: No
a1:
    - An
    - array!
a2:
    associative: array
    key: value
a3:
    mixed: value
    - value
    - value2

EXPECT
);
