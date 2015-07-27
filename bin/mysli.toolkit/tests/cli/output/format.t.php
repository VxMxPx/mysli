<?php

#: Before
use mysli\toolkit\cli\output;

#: Test Format, With Closing
#: Expect Output <<<CLI
output::format("Hello <bold><red>World</bold></red>");
<<<CLI
Hello [1m[31mWorld[0m[39m
CLI;

#: Test Format, Close All
#: Expect Output <<<CLI
output::format("Hello <bold><red>World</all>");
<<<CLI
Hello [1m[31mWorld[0m
CLI;


#: Test Format With Parameters
#: Expect Output <<<CLI
output::format("Hello %s", ["World"]);
<<<CLI
Hello World
CLI;
