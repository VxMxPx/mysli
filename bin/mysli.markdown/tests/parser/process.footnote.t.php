<?php

#: Before
use mysli\markdown;
use mysli\markdown\parser;

# ------------------------------------------------------------------------------
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

# ------------------------------------------------------------------------------
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

# ------------------------------------------------------------------------------
#: Test Multiple, Footnotes
$markdown = <<<MARKDOWN
Hello world! [^first].

Hello world! [^second][^third][^fourth].

[^first]: Footnote **can have markup**
          and multiple lines.
[^second]: Second footnote item.
[^third]: Third with
multiple lines.
[^fourth]: Fourth footnote.
MARKDOWN;
$parser = new parser($markdown);
$parser->process();
$footnote = $parser->get_processor('mysli.markdown.module.footnote');
return assert::equals($footnote->as_array(), [
    'first' => [
        'body' => 'Footnote <strong>can have markup</strong> and multiple lines.',
        'back' => [ 'fnref1' ]
    ],
    'second' => [
        'body' => 'Second footnote item.',
        'back' => [ 'fnref2' ]
    ],
    'third' => [
        'body' => 'Third with multiple lines.',
        'back' => [ 'fnref3' ]
    ],
    'fourth' => [
        'body' => 'Fourth footnote.',
        'back' => [ 'fnref4' ]
    ],
]);

# ------------------------------------------------------------------------------
#: Test Multiple, Footnotes Processed
$markdown = <<<MARKDOWN
Hello world! [^first].

Hello world! [^second][^third][^fourth].

[^first]: Footnote **can have markup**
          and multiple lines.
[^second]: Second footnote item.
[^third]: Third with
multiple lines.
[^fourth]: Fourth footnote.
MARKDOWN;
$parser = new parser($markdown);
return assert::equals(markdown::process($parser),
'<p>Hello world! <sup class="footnote-ref"><a href="#fn1" id="fnref1">[1]</a></sup>.</p>
<p>Hello world! <sup class="footnote-ref"><a href="#fn2" id="fnref2">[2]</a>'.
'</sup><sup class="footnote-ref"><a href="#fn3" id="fnref3">[3]</a>'.
'</sup><sup class="footnote-ref"><a href="#fn4" id="fnref4">[4]</a></sup>.</p>');

# ------------------------------------------------------------------------------
#: Test Footnote With Links
$markdown = <<<MARKDOWN
Hello world! [^first].

Hello world! [^second].

[^first]:  This footnote has [a link](http://domain.tld).
[^second]: Second, http://inline-link.tld
MARKDOWN;
$parser = new parser($markdown);
$parser->process();
$footnote = $parser->get_processor('mysli.markdown.module.footnote');
return assert::equals($footnote->as_array(), [
    'first' => [
        'body' => 'This footnote has <a href="http://domain.tld">a link</a>.',
        'back' => [ 'fnref1' ]
    ],
    'second' => [
        'body' => 'Second, <a href="http://inline-link.tld">http://inline-link.tld</a>',
        'back' => [ 'fnref2' ]
    ]
]);
