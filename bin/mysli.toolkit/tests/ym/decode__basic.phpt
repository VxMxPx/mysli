--TEST--
Real life example.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(<<<EOT
package     : mysli/framework/ym
version     : 1.0.0
description : Simplified YAML (ym) parser.
license     : GPL-3.0
authors:
    - Marko Gajšt (Developer) <m@gaj.st>
require:
    ../type : ~1
    ../fs   : ~1
EOT
));
?>
--EXPECT--
Array
(
    [package] => mysli/framework/ym
    [version] => 1.0.0
    [description] => Simplified YAML (ym) parser.
    [license] => GPL-3.0
    [authors] => Array
        (
            [0] => Marko Gajšt (Developer) <m@gaj.st>
        )

    [require] => Array
        (
            [../type] => ~1
            [../fs] => ~1
        )

)
