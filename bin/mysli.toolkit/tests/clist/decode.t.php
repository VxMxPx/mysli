<?php

#: Before
use mysli\toolkit\clist;

#: Test General
$decoded = clist::decode('
one two
three four
five six
');
return assert::equals(
    $decoded,
    [ ['one', 'two'], ['three', 'four'], ['five', 'six'] ]
);

#: Test Spaced
$decoded = clist::decode('
one   two
three four
');
return assert::equals(
    $decoded,
    [ ['one', 'two'], ['three', 'four'] ]
);

#: Test Multiple Per line
$decoded = clist::decode('
one   two   three   four
five  six   seven   eight
');
return assert::equals(
    $decoded,
    [
        ['one', 'two', 'three', 'four'],
        ['five', 'six', 'seven', 'eight']
    ]
);

#: Test Multiple Per line, Escaped
$decoded = clist::decode('
one   two   three\   four
five  six   seven\   eight
');
return assert::equals(
    $decoded,
    [
        ['one', 'two', 'three   four'],
        ['five', 'six', 'seven   eight']
    ]
);

#: Test Map
$decoded = clist::decode('
mysli.toolkit  1 r160123
mysli.dev.test 2 r160102
mysli.assets   1 r160106
', [ 'map' => [ 'id', 'version', 'release' ] ]);
return assert::equals(
    $decoded,
    [
        [ 'id' => 'mysli.toolkit',  'version' => '1', 'release' => 'r160123' ],
        [ 'id' => 'mysli.dev.test', 'version' => '2', 'release' => 'r160102' ],
        [ 'id' => 'mysli.assets',   'version' => '1', 'release' => 'r160106' ],
    ]
);

#: Test Category
$decoded = clist::decode('
HIGH:
toolkit::web  mysli.front.service::start
MEDIUM:
toolkit::web  mysli.dash.service::first
toolkit::web  mysli.dash.service::last
LOW:
', [ 'category_to' => 'priority', 'categories' => [  'high', 'medium', 'low' ] ]);
return assert::equals(
    $decoded,
    [
        [ '0' => 'toolkit::web', '1' => 'mysli.front.service::start', 'priority' => 'high' ],
        [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::first',  'priority' => 'medium' ],
        [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::last',   'priority' => 'medium' ],
    ]
);

#: Test Category ID
$decoded = clist::decode('
HIGH:
toolkit::web  mysli.front.service::start
MEDIUM:
toolkit::web  mysli.dash.service::first
toolkit::web  mysli.dash.service::last
LOW:
', [ 'category_to' => '{ID}', 'categories' => [  'high', 'medium', 'low' ] ]);
return assert::equals(
    $decoded,
    [
        'high' => [
            [ '0' => 'toolkit::web', '1' => 'mysli.front.service::start' ],
        ],
        'medium' => [
            [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::first' ],
            [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::last' ],
        ]
    ]
);

#: Test Local ID Unique
$decoded = clist::decode('
HIGH:
toolkit::web  mysli.front.service::start
MEDIUM:
toolkit::web  mysli.dash.service::first
toolkit::web  mysli.dash.service::last
LOW:
', [ 'id' => '0', 'category_to' => '{ID}', 'categories' => [  'high', 'medium', 'low' ] ]);
return assert::equals(
    $decoded,
    [
        'high' => [
            'toolkit::web' => [ '0' => 'toolkit::web', '1' => 'mysli.front.service::start' ],
        ],
        'medium' => [
            'toolkit::web' => [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::last' ],
        ]
    ]
);

#: Test Local ID Non-Unique
$decoded = clist::decode('
HIGH:
toolkit::web  mysli.front.service::start
MEDIUM:
toolkit::web  mysli.dash.service::first
toolkit::web  mysli.dash.service::last
LOW:
', [
    'id' => '0',
    'unique' => false,
    'category_to' => '{ID}',
    'categories' => [  'high', 'medium', 'low' ]
]);
return assert::equals(
    $decoded,
    [
        'high' => [
            'toolkit::web' => [
                ['0' => 'toolkit::web', '1' => 'mysli.front.service::start' ]
            ],
        ],
        'medium' => [
            'toolkit::web' => [
                [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::first' ],
                [ '0' => 'toolkit::web', '1' => 'mysli.dash.service::last' ],
            ]
        ]
    ]
);

#: Test Merged, ID Non-Unique
$decoded = clist::decode('
HIGH:
toolkit::web  mysli.front.service::start
MEDIUM:
toolkit::web  mysli.dash.service::first
toolkit::web  mysli.dash.service::last
LOW:
', [
    'id' => 'event',
    'map' => ['event', 'call'],
    'unique' => false,
    'category_to' => 'priority',
    'categories' => [  'high', 'medium', 'low' ]
]);
return assert::equals(
    $decoded,
    [
        'toolkit::web' => [
            [ 'event' => 'toolkit::web', 'call' => 'mysli.front.service::start', 'priority' => 'high' ],
            [ 'event' => 'toolkit::web', 'call' => 'mysli.dash.service::first',  'priority' => 'medium' ],
            [ 'event' => 'toolkit::web', 'call' => 'mysli.dash.service::last',   'priority' => 'medium' ],
        ]
    ]
);
