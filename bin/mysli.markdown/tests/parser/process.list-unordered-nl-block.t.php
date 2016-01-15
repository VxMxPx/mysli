<?php

#: Before
use mysli\markdown;

#: Test List Unordered nl Block
$markdown = <<<MARKDOWN
*   This is a list item with two paragraphs.

    This is the second paragraph in the list item. You're
only required to indent the first line. Lorem ipsum dolor
sit amet, consectetuer adipiscing elit.

*   Another item in the same list.
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<ul>
    <li>
        <p>This is a list item with two paragraphs.</p>
        <p>This is the second paragraph in the list item. You&rsquo;re
            only required to indent the first line. Lorem ipsum dolor
            sit amet, consectetuer adipiscing elit.</p>
    </li>
    <li>Another item in the same list.</li>
</ul>');
