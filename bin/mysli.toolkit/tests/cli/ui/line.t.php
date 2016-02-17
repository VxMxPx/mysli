<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test A Regular Line Out
#: Expect Output <<<CLI
ui::line('Hi!');
<<<CLI
Hi!

CLI;
