<?php

#: Before
use mysli\markdown;

# ------------------------------------------------------------------------------
#: Test Blockquote
$markdown = <<<MARKDOWN
> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.

> Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
id sem consectetuer libero luctus adipiscing.

> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
> consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
> Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<blockquote>
    <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
        consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
        Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
    <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
        id sem consectetuer libero luctus adipiscing.</p>
    <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
        consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
        Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
</blockquote>');

# ------------------------------------------------------------------------------
#: Test Blockquote + HTML
$markdown = <<<MARKDOWN
> Morality is simply the attitude we adopt towards people we personally dislike.
> <br/><small>Oscar Wilde</small>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<blockquote>
    <p>Morality is simply the attitude we adopt towards people we personally dislike.
        <br/><small>Oscar Wilde</small></p>
</blockquote>');
