--TEST--
Syntax error in ym.
--FILE--
<?php
include __DIR__.'/../_common.php';
use mysli\framework\ym as ym;

print_r(ym::decode(<<<EOT
this makes no sense...
EOT
));

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\data' with message 'Error unexpected value: `this makes no sense...` on line: `1`. Colon (:) is required.' in %a
