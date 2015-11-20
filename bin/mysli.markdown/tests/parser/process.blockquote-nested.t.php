<?php

#: Before
use mysli\markdown;

#: Test Blockquote Nested
$markdown = <<<MARKDOWN
> This is the first level of quoting.
>
> > This is nested blockquote.
> > > Three times nested
> > > > Four times
>
> Back to the first level.
>
>
>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<blockquote>
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
</blockquote>');
