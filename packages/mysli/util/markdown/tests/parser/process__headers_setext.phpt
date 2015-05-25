--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF

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

$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<h1>Header 1</h1>

<h2>Header 2</h2>

<h1>Header 1</h1>

<h2>Header 2</h2>
