<?php

#: Before
use mysli\markdown;
use mysli\markdown\parser;

#: Test Basic
$markdown = <<<MARKDOWN
---

-----

  ----

___

_____

***********************
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<hr/>
<hr/>
<hr/>
<hr/>
<hr/>
<hr/>');
