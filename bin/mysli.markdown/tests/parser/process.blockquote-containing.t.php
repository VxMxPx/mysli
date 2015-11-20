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

return assert::equals(markdown::process($markdown), '');
