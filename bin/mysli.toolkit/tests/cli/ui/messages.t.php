<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test Info
#: Expect Output <<<CLI
ui::info('INFO', 'This is an information!');
<<<CLI
[34mINFO:[39m This is an information!

CLI;

# ------------------------------------------------------------------------------
#: Test Info, No Message
#: Expect Output <<<CLI
ui::info('This is an information!');
<<<CLI
[34mThis is an information![39m

CLI;

# ------------------------------------------------------------------------------
#: Test Warning
#: Expect Output <<<CLI
ui::warning('WARNING', 'This is a warning!');
<<<CLI
[33mWARNING:[39m This is a warning!

CLI;

# ------------------------------------------------------------------------------
#: Test Warning, No Message
#: Expect Output <<<CLI
ui::warning('This is a warning!');
<<<CLI
[33mThis is a warning![39m

CLI;

# ------------------------------------------------------------------------------
#: Test Error
#: Expect Output <<<CLI
ui::error('ERROR', 'This is an error!');
<<<CLI
[31mERROR:[39m This is an error!

CLI;

# ------------------------------------------------------------------------------
#: Test Error, No Message
#: Expect Output <<<CLI
ui::error('This is an error!');
<<<CLI
[31mThis is an error![39m

CLI;

# ------------------------------------------------------------------------------
#: Test Success
#: Expect Output <<<CLI
ui::success('SUCCESS', 'Yay!!');
<<<CLI
[32mSUCCESS:[39m Yay!!

CLI;

# ------------------------------------------------------------------------------
#: Test Success, No Message
#: Expect Output <<<CLI
ui::success('Yay!!');
<<<CLI
[32mYay!![39m

CLI;
