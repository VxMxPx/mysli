--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
*   This is a list item with two paragraphs.

    This is the second paragraph in the list item. You're
only required to indent the first line. Lorem ipsum dolor
sit amet, consectetuer adipiscing elit.

*   Another item in the same list.
EOF
);
?>
--EXPECT--
<ul>
    <li>
        <p>This is a list item with two paragraphs.</p>
        <p>This is the second paragraph in the list item. You're
            only required to indent the first line. Lorem ipsum dolor
            sit amet, consectetuer adipiscing elit.</p>
    </li>
    <li>Another item in the same list.</li>
</ul>
