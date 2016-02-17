<?php

#: Before
use \mysli\toolkit\cli\ui;

# ------------------------------------------------------------------------------
#: Test List Aligned
#: Expect Output <<<CLI
ui::list([
  'One' => 'Value One',
  'Two' => 'Value Two',
  'Three' => 'Value Three',
  'Four' => 'Value Four',
], ui::list_aligned);
<<<CLI
One   : "Value One"
Two   : "Value Two"
Three : "Value Three"
Four  : "Value Four"

CLI;

# ------------------------------------------------------------------------------
#: Test List Aligned Indented
#: Expect Output <<<CLI
ui::list([
  'One' => 'Value One',
  'Two' => 'Value Two',
  'Three' => 'Value Three',
  'Four' => 'Value Four',
], ui::list_aligned, 1);
<<<CLI
  One   : "Value One"
  Two   : "Value Two"
  Three : "Value Three"
  Four  : "Value Four"

CLI;

# ------------------------------------------------------------------------------
#: Test List Aligned Multi Dimensions
#: Expect Output <<<CLI
ui::list([
  'One' => 'Value One',
  'Two' => [
    'One' => 'Value One',
    'Two' => 'Value Two',
    'Three' => 'Value Three',
    'Four' => 'Value Four',
  ],
  'Three' => 'Value Three',
  'Four' => 'Value Four',
], ui::list_aligned);
<<<CLI
One   : "Value One"
Two
  One   : "Value One"
  Two   : "Value Two"
  Three : "Value Three"
  Four  : "Value Four"
Three : "Value Three"
Four  : "Value Four"

CLI;
