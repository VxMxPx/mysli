<?php

#: Before
use mysli\markdown;

#: Test List Unordered
$markdown = <<<MARKDOWN
* Red
* Green
* Blue

+ Red
+ Green
+ Blue

- Red
- Green
- Blue
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<ul>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
    <li>Red</li>
    <li>Green</li>
    <li>Blue</li>
</ul>');
