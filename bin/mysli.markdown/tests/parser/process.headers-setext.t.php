<?php

#: Before
use mysli\markdown;

#: Test Headers Setext
$markdown = <<<MARKDOWN
Header 1
=

Header 2
-

Header 1
========

Header 2
--------

MARKDOWN;

return assert::equals(markdown::process($markdown),
'<h1>Header 1</h1>
<h2>Header 2</h2>
<h1>Header 1</h1>
<h2>Header 2</h2>');
