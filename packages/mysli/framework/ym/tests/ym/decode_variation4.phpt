--TEST--
No key array.
--FILE--
<?php
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
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
EOT
));

?>
--EXPECT--
Array
(
    [receipt] => Oz-Ware Purchase Invoice
    [date] => 2012-08-06
    [customer] => Array
        (
            [given] => Dorothy
            [family] => Gale
        )

    [items] => Array
        (
            [0] => Array
                (
                    [part_no] => A4786
                    [descrip] => Water Bucket (Filled)
                    [price] => 1.47
                    [quantity] => 4
                )

            [1] => Array
                (
                    [part_no] => E1628
                    [descrip] => High Heeled "Ruby" Slippers
                    [size] => 8
                    [price] => 100.27
                    [quantity] => 1
                )

        )

)
