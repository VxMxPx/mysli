--TEST--
Real life example.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::encode([
    'package'     => 'mysli.framework.ym',
    'version'     =>  1,
    'description' => 'Simplified YAML (ym) parser.',
    'license'     => 'GPL-3.0',
    'authors'     => [
        'Marko Gajšt (Developer) <m@gaj.st>'
    ],
    'require'     => [
        'mysli.framework.type' => 1,
        'mysli.framework.fs'   => 1,
    ]
]));
?>
--EXPECTF--
package: mysli.framework.ym
version: 1
description: Simplified YAML (ym) parser.
license: GPL-3.0
authors:
    - Marko Gajšt (Developer) <m@gaj.st>
require:
    mysli.framework.type: 1
    mysli.framework.fs: 1
