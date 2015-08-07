<?php

#: Before
use mysli\toolkit\ym;

#: Test Inline Array
$decoded = ym::decode('
numbers: [ one, two, three ]
letters: [ a, b, c ]
');
return assert::equals(
    $decoded,
    [
        "numbers" => [ "one", "two", "three" ],
        "letters" => [ "a", "b", "c" ]
    ]
);

#: Test Inline Array Associative
$decoded = ym::decode('
numbers: [ 1: one, 2: two, three: three ]
letters: [ a: a, b: b, c: c ]
');
return assert::equals(
    $decoded,
    [
        "numbers" => [ 1 => "one", 2 => "two", "three" => "three" ],
        "letters" => [ "a" => "a", "b" => "b", "c" => "c" ]
    ]
);

#: Test Inline Array Associative Quoted
$decoded = ym::decode('
numbers: [ "1: one", "2: two, three: three" ]
letters: [ "a: a", "b: b", c: c ]
');
return assert::equals(
    $decoded,
    [
        "numbers" => [ "1: one", "2: two, three: three" ],
        "letters" => [ "a: a", "b: b", "c" => "c" ]
    ]
);

#: Test Inline Array Nested
$decoded = ym::decode('
fruit: [ yellow: [ bananas, lemons ], red: [ strawberries ] ]
');
return assert::equals(
    $decoded,
    [
        "fruit" => [
                "yellow" => [ "bananas", "lemons" ],
                "red" => [ "strawberries" ]
        ]
    ]
);

#: Test Double No Key
$decoded = ym::decode('
numbers: [ [ one ], [ two ], [ three ] ]
');
return assert::equals(
    $decoded,
    [
        "numbers" => [ [ "one" ], [ "two" ], [ "three" ] ]
    ]
);

#: Test Inline Array Deep Nested
$decoded = ym::decode('
levels: [ l1: [ l2: [ l3 : [ l4: [ l5: [ l6: YO! ] ] ], l3b: [ l4b: MO!, l4b2: LO! ] ] ] ]
');
return assert::equals(
    $decoded,
    [
        "levels" => [
            "l1" => [
                "l2" => [
                    "l3" => [
                        "l4" => [
                            "l5" => [
                                "l6" => "YO!"
                            ]
                        ]
                    ],
                    "l3b" => [
                        "l4b"  => "MO!",
                        "l4b2" => "LO!"
                    ]
                ]
            ]
        ]
    ]
);

#: Test Empty Array
$decoded = ym::decode('
require: []
');
return assert::equals(
    $decoded, [ 'require' => [] ]
);

#: Test Trim
$decoded = ym::decode('
items: [ "  spacing ", "    spaced key" : " value    ",       spaced trimmed:         value ]
');
return assert::equals(
    $decoded,
    [
        "items" => [
            "  spacing ",
            "    spaced key" => " value    ",
            "spaced trimmed" => "value"
        ]
    ]
);

#: Test Types
$decoded = ym::decode('
types: [ I\'m string!, "I\'m also a string", "113700", 42, -42, 11.23, -11.23, Yes, No, True, False ]
');
return assert::equals(
    $decoded,
    [ 'types' => [
        'I\'m string!', 'I\'m also a string', "113700", 42, -42, 11.23, -11.23,
        true, false, true, false,
    ]]
);

# EXCEPTIONS -------------------------------------------------------------------

#: Test Exception, Unclosed Array
#: Expect Exception mysli\toolkit\exception\ym 20
ym::decode('numbers: [ "1: one", "2: two, three: three"');

#: Test Unexpected Colon
#: Expect Exception mysli\toolkit\exception\ym 10
ym::decode('numbers: [ : ]');

#: Test Unexpected Colon
#: Expect Exception mysli\toolkit\exception\ym 9
ym::decode('numbers: [ foo :: bar ]');

#: Test Unclosed Sub Array
#: Expect Exception mysli\toolkit\exception\ym 20
ym::decode('numbers: [ foo: [ bar ]');

#: Test Double Array, No Comma
#: Expect Exception mysli\toolkit\exception\ym 9
return ym::decode('numbers: [ one: one two: two ]');

#: Test Double Array, No Comma
#: Expect Exception mysli\toolkit\exception\ym 5
return ym::decode('numbers: [ x: [ foo ] y: [ bar ] ]');
