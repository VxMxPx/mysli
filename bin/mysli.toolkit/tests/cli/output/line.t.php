<?php

#: Before
use mysli\toolkit\cli\output;


#: Test A Regular Line Out
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Expect Output <<<CLI
output::line("Hi!");
<<<CLI
Hi!

CLI;


#: Test No New Line Out
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Expect Output <<<CLI
output::line("Hi...", false);
output::line("there!", false);
<<<CLI
Hi...there!
CLI;
