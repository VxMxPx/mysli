<?php

#: Before
use mysli\dev\test\diff;

# ------------------------------------------------------------------------------
#: Test Plain
return assert::equals(diff::generate([
    'Hello World',
    12,
    12.3,
    [ 'pear', 'banana', 'pineapple' ],
    Null,
    false,
    true,
    new \ArrayObject(),
], [
    'Hello World',
    13,
    13.3,
    [ 'banana', 'pear', 'pineapple' ],
    Null,
    true,
    false,
    new \ArrayIterator(),
]), [
    [ false, 1, '"Hello World"',        '"Hello World"' ],
    [ true,  1, 12,                     13 ],
    [ true,  1, 12.3,                   13.3 ],
    [ true,  2, '0: "pear"',            '0: "banana"' ],
    [ true,  2, '1: "banana"',          '1: "pear"' ],
    [ false, 2, '2: "pineapple"',       '2: "pineapple"' ],
    [ false, 1, '[null]',               '[null]' ],
    [ true,  1, "[false]",              '[true]' ],
    [ true,  1, "[true]",               '[false]' ],
    [ true,  1, "[object:ArrayObject]", '[object:ArrayIterator]' ],
]);


# ------------------------------------------------------------------------------
#: Test Plain, Written Type
return assert::equals(diff::generate([
    null,
    true,
    new \ArrayObject(),
], [
    '[null]',
    '[true]',
    '[object:ArrayObject]'
]), [
    [ true,  1, "[null]", '"[null]"' ],
    [ true,  1, "[true]", '"[true]"' ],
    [ true,  1, "[object:ArrayObject]", '"[object:ArrayObject]"' ],
]);
