--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
Header 1
=

Header 2
-

Header 1
========

Header 2
--------

EOF
);
?>
--EXPECT--
<h1>Header 1</h1>
<h2>Header 2</h2>
<h1>Header 1</h1>
<h2>Header 2</h2>
