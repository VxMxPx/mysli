--TEST--
--FILE--
<?php

use mysli\framework\type\str as str;

var_dump(str::to_unix_line_endings("Hello\r\nWorld") === "Hello\nWorld");
var_dump(
    str::to_unix_line_endings(
        "Hello\r\n\r\n\r\n\r\n\r\n\r\n\r\nWorld", true) === "Hello\n\nWorld");

?>
--EXPECT--
bool(true)
bool(true)
