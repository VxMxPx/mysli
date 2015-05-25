--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF
1. One
2. Two
3. Three
4. Four
5. Five

1. One
1. Two
1. Three
1. Four
1. Five

100000. One
200000. Two
300000. Three
400000. Four
500000. Five
EOF
);

$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<ol>
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>

<ol>
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>

<ol>
<li>One</li>
<li>Two</li>
<li>Three</li>
<li>Four</li>
<li>Five</li>
</ol>
