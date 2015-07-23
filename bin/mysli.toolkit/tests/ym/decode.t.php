<?php

#: Before
use mysli\toolkit\ym;

#: Test General
$decoded = ym::decode('
package:     mysli.toolkit
version:     1
description: Mysli Toolkit Core Library.
license:     GPL-3.0
authors:
    - Marko Gajšt (Developer) <m@gaj.st>
require: No
');
return assert::equals(
    $decoded,
    [
        'package' => 'mysli.toolkit',
        'version' => 1,
        'description' => 'Mysli Toolkit Core Library.',
        'license' => 'GPL-3.0',
        'authors' => [ "Marko Gajšt (Developer) <m@gaj.st>" ],
        'require' => false
    ]
);

#: Test Comments
$decoded = ym::decode('
# Comment 1
k1 : value
# Comment 2
k2 : value
# Comment 3
k3 : value
');
return assert::equals(
    $decoded,
    [
        'k1' => 'value',
        'k2' => 'value',
        'k3' => 'value',
    ]
);

#: Test Spacing
$decoded = ym::decode('
k1 : value

k2 : value

k3 : value
');
return assert::equals(
    $decoded,
    [
        'k1' => 'value',
        'k2' => 'value',
        'k3' => 'value',
    ]
);

#: Test Empty Array
$decoded = ym::decode('
name: bar
require: []
line: foo
');
return assert::equals(
    $decoded,
    [
        'name' => 'bar',
        'require' => [],
        'line' => 'foo'
    ]
);

#: Test Empty File
$decoded = ym::decode('');
return assert::equals($decoded, []);

#: Test Exception Missing Colon
#: Expect Exception mysli\toolkit\exception\ym 10
$decoded = ym::decode('
Hello World!
');

#: Test Identical Keys
$decoded = ym::decode('
key : String
key :
    - an
    - array
key : 42
');
return assert::equals(
    $decoded,
    [
        'key' => 42
    ]
);

#: Test Key Hash
$decoded = ym::decode('
# Comment 1
"#k1" : value
# Comment 2
"#k2" : value
# Comment 3
"#k3" : value
');
return assert::equals(
    $decoded,
    [
        '#k1' => 'value',
        '#k2' => 'value',
        '#k3' => 'value',
    ]
);

#: Test No Keys
$decoded = ym::decode('
- one
- two
- three
');
return assert::equals(
    $decoded,
    [ 'one', 'two', 'three' ]
);

#: Test List With Colon
$decoded = ym::decode('
level1:
    - Foo: Yes
    - list: item 1
    - list: item 2
');
return assert::equals(
    $decoded,
    [
        'level1' =>
        [
            'Foo: Yes',
            'list: item 1',
            'list: item 2',
        ]
    ]
);

#: Test Deep Nesting
$decoded = ym::decode('
level1a : 1a
level1b :
    level2a:
        level3a: 3a
        level3b: 3b
    level2b: 2b
    level2c:
        level3a: 3a
        level3b:
            - one
            - two
            - three
            - four
    level2d:
        - one
        - two
level1c:
    level2a: 2b
');
return assert::equals(
    $decoded,
    [
        'level1a' => '1a',
        'level1b' =>
        [
            'level2a' => [ 'level3a' => '3a', 'level3b' => '3b', ],
            'level2b' => '2b',
            'level2c' =>
            [
                'level3a' => '3a',
                'level3b' => [ 'one', 'two', 'three', 'four' ],
            ],
            'level2d' => [ 'one', 'two' ],
        ],
        'level1c' => [ 'level2a' => '2b' ]
    ]
);

#: Test No Key Array
$decoded = ym::decode('
receipt:     Oz-Ware Purchase Invoice
date:        2012-08-06
customer:
    given:   Dorothy
    family:  Gale

items:
    -
        part_no:  A4786
        descrip:  Water Bucket (Filled)
        price:    1.47
        quantity: 4
    -
        part_no:   E1628
        descrip:   High Heeled "Ruby" Slippers
        size:      8
        price:     100.27
        quantity:  1
');
return assert::equals(
    $decoded,
    [
        'receipt' => 'Oz-Ware Purchase Invoice',
        'date' => '2012-08-06',
        'customer' =>
        [
            'given' => 'Dorothy',
            'family' => 'Gale',
        ],
        'items' =>
        [
            [
                'part_no' => 'A4786',
                'descrip' => 'Water Bucket (Filled)',
                'price' => 1.47,
                'quantity' => 4
            ],
            [
                'part_no' => 'E1628',
                'descrip' => 'High Heeled "Ruby" Slippers',
                'size' => 8,
                'price' => 100.27,
                'quantity' => 1
            ]
        ]
    ]
);

#: Test Types
$decoded = ym::decode('
s1: I\'m string!
s2: "I\'m also a string"
s3: "113700"
i1: 42
i2: -42
f1: 11.23
f2: -11.23
b1: Yes
b2: No
b3: True
b4: False
a1:
    - An
    - array!
a2:
    associative : array
    key: value
a3:
    mixed: value
    - value
    - value2
');
return assert::equals(
    $decoded,
    [
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
    ]
);

#: Test Deep Nesting No Key
$decoded = ym::decode('
level1:
    level2:
        level3:
            - one
            - two
-
    -
        - one
        - two
        -
            key : value
');
return assert::equals(
    $decoded,
    [
        'level1' =>
        [
            'level2' =>
            [
                'level3' => [ 'one', 'two' ]
            ]
        ],
        1 =>
        [
            [ 'one', 'two', [ 'key' => 'value' ] ]
        ]
    ]
);
