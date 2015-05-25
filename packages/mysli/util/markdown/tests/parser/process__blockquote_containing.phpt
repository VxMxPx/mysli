--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF
> ## This is a header.
>
> 1.   This is the first list item.
> 2.   This is the second list item.
>
> Here's some example code:
>
>     return shell_exec("echo \$input | \$markdown_script");
EOF
);


$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<blockquote>
<h2>This is a header.</h2>

<ol>
<li>This is the first list item.</li>
<li>This is the second list item.</li>
</ol>

<p>Here's some example code:</p>

<pre><code>return shell_exec("echo $input | $markdown_script");</code></pre>
</blockquote>
