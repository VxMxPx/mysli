<?php

#: Before
use mysli\markdown;
use mysli\markdown\parser;

#: Test Basic, Inline
$markdown = <<<MARKDOWN
Hello world! [^first].

Hello world ^[Woo, inline footnote!]

Reference back to the first one! [^first]

[^first]: Footnote **can have markup**
          and multiple lines.
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>Hello world! <sup class="footnote-ref"><a href="#fn1" id="fnref1">[1]</a></sup>.</p>
<p>Hello world <sup class="footnote-ref"><a href="#fn2" id="fnref2">[2]</a></sup></p>
<p>Reference back to the first one! <sup class="footnote-ref"><a href="#fn1" id="fnref1:1">[1]</a></sup></p>');

#: Test Basic, Footnotes
$markdown = <<<MARKDOWN
Hello world! [^first].

Hello world ^[Woo, inline footnote!]

Reference back to the first one! [^first]

[^first]: Footnote **can have markup**
          and multiple lines.
MARKDOWN;

$parser = new parser($markdown);
$parser->process();
$footnote = $parser->get_processor('mysli.markdown.module.footnote');

return assert::equals($footnote->as_array(), [
    'first' => [
        'body' => 'Footnote <strong>can have markup</strong> and multiple lines.',
        'back' => [ 'fnref1', 'fnref1:1' ]
    ],
    'auto-fn-1' => [
        'body' => 'Woo, inline footnote!',
        'back' => [ 'fnref2' ]
    ],
]);
