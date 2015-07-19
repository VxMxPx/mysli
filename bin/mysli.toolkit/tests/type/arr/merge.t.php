<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Nested
$array1 = [
    'a' => 1,
    'b' => [
        'b1' => 21,
        'b2' => 22,
        'b3' => [
            'b31' => 220,
            'b32' => [1, 2, 3]
        ]
    ]
];
$array2 = [
    'b' => [
        'b1' => 24,
        'b3' => [
            'b31' => 231,
            'b32' => [4, 5, 6]
        ]
    ],
    'c' => 3,
    'd' => 4
];

#: Test Basic
$array1 = ["color" => "red", 2, 4];
$array2 = ["a", "b", "color" => "green", "shape" => "trapezoid", 4];
return assert::equals(
    arr::merge($array1, $array2),
    [ 'color' => 'green', 'a',  'b', 'shape' => 'trapezoid', 4]
);

#: Test Merge All
$array1 = ["color" => "red", 2, 4];
$array2 = ["a", "b", "color" => "green", "shape" => "trapezoid", 4];
return assert::equals(
    arr::merge($array1, $array2, arr::merge_all),
    [ 'color' => 'green', 'a', 'b', 'shape' => 'trapezoid', 2 => 4 ]
);

#: Test Multi Dimentional
$array1 = ["color" => ["favorite" => "red"], 5];
$array2 = [10, "color" => ["favorite" => "green", "blue"]];
return assert::equals(
    arr::merge($array1, $array2),
    [ 'color' => [ 'favorite' => 'green', 'blue' ], 10 ]
);

#: Test Multi Dimentional, Merge All
$array1 = ["color" => ["favorite" => "red"], 5];
$array2 = [10, "color" => ["favorite" => "green", "blue"]];
return assert::equals(
    arr::merge($array1, $array2),
    [ 'color' => [ 'favorite' => 'green', 'blue' ], 10 ]
);

#: Test Non Associative
$array1 = [0, 1, 2, 3];
$array2 = [0, 1, 2, 3];
return assert::equals(
    arr::merge($array1, $array2),
    [ 0, 1, 2, 3, 0, 1, 2, 3 ]
);

#: Test Non Associative, Merge All
$array1 = [0, 1, 2, 3];
$array2 = [0, 1, 2, 3];
return assert::equals(
    arr::merge($array1, $array2, arr::merge_all),
    [ 0, 1, 2, 3 ]
);

#: Test Non Associative, Non-Sequential
$array1 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
$array2 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
return assert::equals(
    arr::merge($array1, $array2),
    [ 0, 1, 2, 3, 0, 1, 2, 3 ]
);

#: Test Non Associative, Non-Sequential, Merge All
$array1 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
$array2 = [255 => 0, 256 => 1, 257 => 2, 258 => 3];
return assert::equals(
    arr::merge($array1, $array2, arr::merge_all),
    [ 255 => 0, 256 => 1, 257 => 2, 258 => 3 ]
);

#: Test Deep Nested
#: Use Nested
return assert::equals(
    arr::merge($array1, $array2),
    [
        'a' => 1,
        'b' => [
            'b1' => 24,
            'b2' => 22,
            'b3' => [
                'b31' => 231,
                'b32' => [ 1, 2, 3, 4, 5, 6]
            ],
        ],
        'c' => 3,
        'd' => 4,
    ]
);

#: Test Deep Nested, Merge All
#: Use Nested
return assert::equals(
    arr::merge($array1, $array2, arr::merge_all),
    [
        'a' => 1,
        'b' => [
            'b1' => 24,
            'b2' => 22,
            'b3' => [
                'b31' => 231,
                'b32' => [ 4, 5, 6]
            ],
        ],
        'c' => 3,
        'd' => 4,
    ]
);

#: Test At Least Two Parameters Are Needed
#: Expect Exception mysli\toolkit\exception\arr 10
arr::merge([]);

#: Test Invalid Merge Type
#: Expect Exception mysli\toolkit\exception\arr 20
arr::merge([], [], ':>');

#: Test All Parameters But Last Needs to be an Array
#: Expect Exception mysli\toolkit\exception\arr 30
arr::merge([], [], 12, null);
