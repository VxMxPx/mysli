<?php

#: Before
use mysli\dev\test\diff;

#: Test Plain
return assert::equals(diff::plain([
    'Hello World',
    12,
    12.3,
    [ 'pear', 'banana', 'pineapple' ],
    Null,
    false,
    true,
    new \ArrayObject(),
]), [
    [ false, 1, '"Hello World"',        null ],
    [ false, 1, 12,                     null ],
    [ false, 1, 12.3,                   null ],
    [ false, 2, '0: "pear"',            null ],
    [ false, 2, '1: "banana"',          null ],
    [ false, 2, '2: "pineapple"',       null ],
    [ false, 1, '[null]',               null ],
    [ false, 1, "[false]",              null ],
    [ false, 1, "[true]",               null ],
    [ false, 1, "[object:ArrayObject]", null ],
]);
