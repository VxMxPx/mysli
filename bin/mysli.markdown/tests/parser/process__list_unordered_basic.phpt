--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
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
?>
--EXPECT--
<ul>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
</ul>
