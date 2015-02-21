--TEST--
Syntax error in ym.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::decode(<<<EOT
this makes no sense...
EOT
));

?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\framework\exception\parser' with message 'Missing colon (:) or dash (-).
>>1. this makes no sense...
%a
