<?php

#: Before
use mysli\markdown;

#: Test Basic
$markdown = <<<MARKDOWN
This is HTML and CSS abbreviation example.

It converts "HTML", but keep intact HTML partial entries like xxxHTMLyyy.

HTML-9

*[HTML]: Hyper Text Markup Language
*[CSS]: Cascading Style Sheets
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>This is <abbr title="Hyper Text Markup Language">HTML</abbr> and <abbr title="Cascading Style Sheets">CSS</abbr> abbreviation example.</p>
<p>It converts &ldquo;<abbr title="Hyper Text Markup Language">HTML</abbr>&rdquo;, but keep intact <abbr title="Hyper Text Markup Language">HTML</abbr> partial entries like xxxHTMLyyy.</p>
<p><abbr title="Hyper Text Markup Language">HTML</abbr>-9</p>');
