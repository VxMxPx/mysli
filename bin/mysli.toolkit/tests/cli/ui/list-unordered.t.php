<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test List Unordered
#: Expect Output <<<CLI
ui::list(['One', 'Two', 'Three', 'Four'], ui::list_unordered);
<<<CLI
- One
- Two
- Three
- Four

CLI;

# ------------------------------------------------------------------------------
#: Test List Unordered Indented
#: Expect Output <<<CLI
ui::list(['One', 'Two', 'Three', 'Four'], ui::list_unordered, 1);
<<<CLI
  - One
  - Two
  - Three
  - Four

CLI;

# ------------------------------------------------------------------------------
#: Test List Unordered Multi Dimensions
#: Expect Output <<<CLI
ui::list([
    'One',
    [ 'A', 'B', 'C' ],
    'Two',
    'Three',
    'Four'
], ui::list_unordered);
<<<CLI
- One
  - A
  - B
  - C
- Two
- Three
- Four

CLI;
