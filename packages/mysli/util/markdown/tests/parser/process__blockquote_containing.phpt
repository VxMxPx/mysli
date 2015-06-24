--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
> ## This is a header.
>
> 1. This is the first list item.
>       - One
>       - Two
>       - Three
> 2. This is the second list item.
>
>
> Here's some example code:
>
>     return shell_exec("echo \$input | \$markdown_script");
EOF
);
?>
--EXPECT--
