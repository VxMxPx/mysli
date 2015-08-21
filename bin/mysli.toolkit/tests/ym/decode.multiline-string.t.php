<?php

#: Before
use mysli\toolkit\ym;

#: Test Multiline String (>)
$decoded = ym::decode('
multiline: >
    Lorem ipsum dolor sit amet,
    tempor incididunt ut labore
    quis nostrud exercitation ullamco.
');
return assert::equals(
    $decoded,
    [
        "multiline" => "Lorem ipsum dolor sit amet, tempor incididunt ut labore quis nostrud exercitation ullamco."
    ]
);

#: Test Multiline String (|)
$decoded = ym::decode('
multiline: |
    Lorem ipsum dolor sit amet,
    tempor incididunt ut labore
    quis nostrud exercitation ullamco.
');
return assert::equals(
    $decoded,
    [
        "multiline" => "Lorem ipsum dolor sit amet,\ntempor incididunt ut labore\nquis nostrud exercitation ullamco."
    ]
);

#: Test Multiline String (")
$decoded = ym::decode('
multiline: "Lorem ipsum dolor sit amet,
tempor incididunt ut labore
quis nostrud exercitation ullamco."
');
return assert::equals(
    $decoded,
    [
        "multiline" => "Lorem ipsum dolor sit amet, tempor incididunt ut labore quis nostrud exercitation ullamco."
    ]
);
