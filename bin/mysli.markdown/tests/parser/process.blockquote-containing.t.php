<?php

#: Before
use mysli\markdown;

#: Test Blockquote Containing
$markdown = <<<MARKDOWN
> ## This is a header.
>
> 1. This is the first list item.
>       - One
>       - Two
>       - Three
> 2. This is the second list item.
>
>
> Here\'s some example code:
>
>     return shell_exec("echo \$input | \$markdown_script");
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<blockquote>
    <h2>This is a header.</h2>
    <ol>
        <li>
            <p>This is the first list item.</p>
            <ul>
                <li>One</li>
                <li>Two</li>
                <li>Three</li>
            </ul>
        </li>
        <li>This is the second list item.</li>
    </ol>
    <p>Here\'s some example code:</p>
    <code>return shell_exec("echo $input | $markdown_script");</code>
 </blockquote>');
