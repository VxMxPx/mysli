--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown\parser;

$parser = new parser(<<<EOF
> This is the first level of quoting.
>
> > This is nested blockquote.
> > > Three times nested
> > > > Four times
> Back to the first level.
>
>
>
EOF
);

$parser->process();
echo $parser->as_string();

?>
--EXPECT--
<blockquote>
<p>This is the first level of quoting.</p>

<blockquote>
<p>This is nested blockquote.</p>
<blockquote>
<p>Three times nested</p>
<blockquote>
<p>Four times</p>
</blockquote>
</blockquote>
</blockquote>
<p>Back to the first level.</p>



</blockquote>
