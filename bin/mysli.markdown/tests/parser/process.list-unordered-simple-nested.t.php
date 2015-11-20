<?php

#: Before
use mysli\markdown;

#: Test List Unordered Simple Nested
$markdown = <<<MARKDOWN
- One
    - Two
        - Two B
    - Three
- Four
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<ul>
    <li>One
        <ul>
            <li>Two
                <ul>
                    <li>Two B</li>
                </ul>
            </li>
            <li>Three</li>
        </ul>
    </li>
    <li>Four</li>
</ul>');
