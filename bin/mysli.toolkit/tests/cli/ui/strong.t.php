<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test Strong Line
#: Expect Output <<<CLI
ui::strong('Hello!');
<<<CLI
[1mHello![0m

CLI;
