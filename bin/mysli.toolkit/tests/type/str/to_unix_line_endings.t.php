<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
return assert::equals(
    str::to_unix_line_endings("Hello\r\nWorld"),
    "Hello\nWorld"
);

#: Test Many Breaks, Remove
return assert::equals(
    str::to_unix_line_endings("Hello\r\n\r\n\r\n\r\n\r\n\r\n\r\nWorld", true),
    "Hello\n\nWorld"
);
