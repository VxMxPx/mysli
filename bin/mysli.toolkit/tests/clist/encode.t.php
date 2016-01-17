<?php

#: Before
use mysli\toolkit\clist;

#: Test General
# ~~~~~~~~~~~~~
$encoded = clist::encode([ ['one', 'two'], ['three', 'four'], ['five', 'six'] ]);
return assert::equals($encoded,
'one   two
three four
five  six
');

#: Test Multiple Per line
# ~~~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        ['one', 'two', 'three', 'four'],
        ['five', 'six', 'seven', 'eight']
    ]
);
return assert::equals($encoded,
'one  two three four
five six seven eight
');

#: Test Multiple Per line, Escaped
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        ['one', 'two', 'three   four'],
        ['five', 'six', 'seven   eight nine ten']
    ]
);
return assert::equals($encoded,
'one  two three\   four
five six seven\   eight\ nine\ ten
');

#: Test Map
# ~~~~~~~~~
$encoded = clist::encode(
    [
        [ 'id' => 'mysli.toolkit',  'version' => '1', 'release' => 'r160123', 'junk' => 'junk foo' ],
        [ 'id' => 'mysli.dev.test', 'version' => '2', 'release' => 'r160102', 'junk' => 'junk foo' ],
        [ 'id' => 'mysli.assets',   'version' => '1', 'release' => 'r160106', 'junk' => 'junk foo' ],
    ],
    [ 'map' => [ 'id', 'version', 'release' ] ]
);
return assert::equals($encoded,
'mysli.toolkit  1 r160123
mysli.dev.test 2 r160102
mysli.assets   1 r160106
');

#: Test Category
# ~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        [ 'toolkit::web', 'mysli.front.service::start', 'priority' => 'high' ],
        [ 'toolkit::web', 'mysli.dash.service::first',  'priority' => 'medium' ],
        [ 'toolkit::web', 'mysli.dash.service::last',   'priority' => 'medium' ],
    ],
    [ 'category_to' => 'priority', 'categories' => [  'high', 'medium', 'low' ] ]
);
return assert::equals($encoded,
'HIGH:
toolkit::web mysli.front.service::start
MEDIUM:
toolkit::web mysli.dash.service::first
toolkit::web mysli.dash.service::last
LOW:
');

#: Test Category ID
# ~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        'high' => [
            [ 'toolkit::web', 'mysli.front.service::start' ],
        ],
        'medium' => [
            [ 'toolkit::web', 'mysli.dash.service::first' ],
            [ 'toolkit::web', 'mysli.dash.service::last' ],
        ]
    ],
    [ 'category_to' => '{ID}', 'categories' => [  'high', 'medium', 'low' ] ]
);
return assert::equals($encoded,
'HIGH:
toolkit::web mysli.front.service::start
MEDIUM:
toolkit::web mysli.dash.service::first
toolkit::web mysli.dash.service::last
LOW:
');

#: Test Local ID Unique
# ~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        'high' => [
            'toolkit::web' => [ 'toolkit::web', 'mysli.front.service::start' ],
        ],
        'medium' => [
            'toolkit::web' => [ 'toolkit::web', 'mysli.dash.service::last' ],
        ]
    ],
    [ 'id' => '0', 'category_to' => '{ID}', 'categories' => [  'high', 'medium', 'low' ] ]
);
return assert::equals($encoded,
'HIGH:
toolkit::web mysli.front.service::start
MEDIUM:
toolkit::web mysli.dash.service::last
LOW:
');

#: Test Local ID, Second Unique
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        'high' => [
            'toolkit::web' => [ 'toolkit::web', 'mysli.front.service::start' ],
        ],
        'medium' => [
            'toolkit::web' => [ 'toolkit::web', 'mysli.dash.service::last' ],
        ]
    ],
    [ 'id' => '1', 'category_to' => '{ID}', 'categories' => [  'high', 'medium', 'low' ] ]
);
return assert::equals($encoded,
'HIGH:
mysli.front.service::start toolkit::web
MEDIUM:
mysli.dash.service::last toolkit::web
LOW:
');

#: Test Local, ID Non-Unique
# ~~~~~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        'high' => [
            'toolkit::web' => [
                [ 'toolkit::web', 'mysli.front.service::start' ]
            ],
        ],
        'medium' => [
            'toolkit::web' => [
                [ 'toolkit::web', 'mysli.dash.service::first' ],
                [ 'toolkit::web', 'mysli.dash.service::last' ],
            ]
        ]
    ],
    [
        'id'          => '0',
        'category_to' => '{ID}',
        'categories'  => [  'high', 'medium', 'low' ]
    ]
);
return assert::equals($encoded,
'HIGH:
toolkit::web mysli.front.service::start
MEDIUM:
toolkit::web mysli.dash.service::first
toolkit::web mysli.dash.service::last
LOW:
');

#: Test Merged, ID Non-Unique
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~
$encoded = clist::encode(
    [
        'toolkit::web' => [
            [ 'event' => 'toolkit::web', 'call' => 'mysli.front.service::start', 'priority' => 'high' ],
            [ 'event' => 'toolkit::web', 'call' => 'mysli.dash.service::first',  'priority' => 'medium' ],
            [ 'event' => 'toolkit::web', 'call' => 'mysli.dash.service::last',   'priority' => 'medium' ],
        ]
    ],
    [
        'id'          => 'event',
        'map'         => ['event', 'call'],
        'category_to' => 'priority',
        'categories'  => [  'high', 'medium', 'low' ]
    ]
);
return assert::equals($encoded,
'HIGH:
toolkit::web mysli.front.service::start
MEDIUM:
toolkit::web mysli.dash.service::first
toolkit::web mysli.dash.service::last
LOW:
');
