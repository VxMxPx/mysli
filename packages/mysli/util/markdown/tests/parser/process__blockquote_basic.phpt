--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.

> Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
id sem consectetuer libero luctus adipiscing.

> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
> consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
> Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.
EOF
);

?>
--EXPECT--
<blockquote>
    <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
        consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
        Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
    <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
        id sem consectetuer libero luctus adipiscing.</p>
    <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
        consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
        Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
</blockquote>
