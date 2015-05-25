--TEST--
Test proper boolean conversion.
--FILE--
<?php
use \mysli\framework\ym\ym;

print_r(ym::encode([
    true, false, 'Yes', 'No', 'True', 'False'
]));
?>
--EXPECTF--
- Yes
- No
- "Yes"
- "No"
- "Yes"
- "No"
