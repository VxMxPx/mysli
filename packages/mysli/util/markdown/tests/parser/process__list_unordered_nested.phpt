--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF
* Red
    * Default
    * Light
    * Dark
* Green
* Blue

+ Red
    + Default
    + Light
    + Dark
        + Saturated
        + Desaturated
+ Green
+ Blue

- Red
- Green
- Blue
    - Default
        - Saturated
            - Dark
            - Light
        - Desaturated
            - Dark
            - Light
    - Light
        - Saturated
    - Dark
EOF
);

$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<ul>
<li>Red
<ul>
<li>Default</li>
<li>Light</li>
<li>Dark</li>
</ul></li>
<li>Green</li>
<li>Blue</li>
</ul>

<ul>
<li>Red
<ul>
<li>Default</li>
<li>Light</li>
<li>Dark
<ul>
<li>Saturated</li>
<li>Desaturated</li>
</ul></li>
</ul></li>
<li>Green</li>
<li>Blue</li>
</ul>

<ul>
<li>Red</li>
<li>Green</li>
<li>Blue
<ul>
<li>Default
<ul>
<li>Saturated
<ul>
<li>Dark</li>
<li>Light</li>
</ul></li>
<li>Desaturated
<ul>
<li>Dark</li>
<li>Light</li>
</ul></li>
</ul></li>
<li>Light
<ul>
<li>Saturated</li>
</ul></li>
<li>Dark</li>
</ul></li>
</ul>
