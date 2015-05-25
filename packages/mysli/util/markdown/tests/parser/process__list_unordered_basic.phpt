--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF
* Red
* Green
* Blue

+ Red
+ Green
+ Blue

- Red
- Green
- Blue
EOF
);

$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<ul>
<li>Red</li>
<li>Green</li>
<li>Blue</li>
</ul>

<ul>
<li>Red</li>
<li>Green</li>
<li>Blue</li>
</ul>

<ul>
<li>Red</li>
<li>Green</li>
<li>Blue</li>
</ul>
