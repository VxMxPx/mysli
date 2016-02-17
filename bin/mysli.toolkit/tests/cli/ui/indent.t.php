<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test Indent
#: Expect Output <<<CLI
ui::line('One', 1);
ui::line('Two', 2);
ui::line('Three', 3);
ui::line('Four');
<<<CLI
  One
    Two
      Three
Four

CLI;
