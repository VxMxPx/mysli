<?php

#: Before
use mysli\markdown;

#: Test Basic
$markdown = <<<MARKDOWN
Characters: (c), (r), (tm), (p), +-

Sentance...

Dashes - and -- and ---

Too many!!!!!!!!

Tooo many???????

That's a nice 'quote right here'.

That's a nice "quote right here".

"Hi there!
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>Characters: &copy;, &reg;, &trade;, &sect;, &plusmn;</p>
<p>Sentance&hellip;</p>
<p>Dashes - and &ndash; and &mdash;</p>
<p>Too many!!!</p>
<p>Tooo many???</p>
<p>That&rsquo;s a nice &lsquo;quote right here&rsquo;.</p>
<p>That&rsquo;s a nice &ldquo;quote right here&rdquo;.</p>
<p>&quot;Hi there!</p>');

#: Test Consider Tags
$markdown = <<<MARKDOWN
<div class="main" style="color:red;">
    "Hello World!"
</div>

<img src="/path/to/image.jpg" alt="" />
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<div class="main" style="color:red;">
&ldquo;Hello World!&rdquo;
</div>
<p><img src="/path/to/image.jpg" alt="" /></p>');

#: Test Multiline
$markdown = <<<MARKDOWN
Lorem: "Ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut".

Labore: 'Et dolore magna aliqua. Ut enim's ad minim veniam,
quis nostrud's exercitation ullamco' laboris nisi's ut aliquip.
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>Lorem: &ldquo;Ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut&rdquo;.</p>
<p>Labore: &lsquo;Et dolore magna aliqua. Ut enim&rsquo;s ad minim veniam,
    quis nostrud&rsquo;s exercitation ullamco&rsquo; laboris nisi&rsquo;s ut aliquip.</p>');
