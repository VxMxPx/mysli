--TEST--
--FILE--
<?php

use mysli\framework\type\arr as arr;
$ar1 = [
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
$ar2 = [
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
print_r(arr::merge($ar1, $ar2));
print_r(arr::merge($ar1, $ar2, arr::merge_all));
?>
--EXPECT--
Array
(
    [a] => 1
    [b] => Array
        (
            [b1] => 24
            [b2] => 22
            [b3] => Array
                (
                    [b31] => 231
                    [b32] => Array
                        (
                            [0] => 1
                            [1] => 2
                            [2] => 3
                            [3] => 4
                            [4] => 5
                            [5] => 6
                        )

                )

        )

    [c] => 3
    [d] => 4
)
Array
(
    [a] => 1
    [b] => Array
        (
            [b1] => 24
            [b2] => 22
            [b3] => Array
                (
                    [b31] => 231
                    [b32] => Array
                        (
                            [0] => 4
                            [1] => 5
                            [2] => 6
                        )

                )

        )

    [c] => 3
    [d] => 4
)
