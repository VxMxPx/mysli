<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test List Ordered
#: Expect Output <<<CLI
ui::lst(['One', 'Two', 'Three', 'Four'], ui::list_ordered);
<<<CLI
1. One
2. Two
3. Three
4. Four

CLI;

# ------------------------------------------------------------------------------
#: Test List Ordered Indented
#: Expect Output <<<CLI
ui::lst(['One', 'Two', 'Three', 'Four'], ui::list_ordered, 1);
<<<CLI
  1. One
  2. Two
  3. Three
  4. Four

CLI;

# ------------------------------------------------------------------------------
#: Test List Ordered Multi Dimensions
#: Expect Output <<<CLI
ui::lst([
    'One',
    [ 'A', 'B', 'C' ],
    'Two',
    'Three',
    'Four'
], ui::list_ordered);
<<<CLI
1. One
  1. A
  2. B
  3. C
2. Two
3. Three
4. Four

CLI;
