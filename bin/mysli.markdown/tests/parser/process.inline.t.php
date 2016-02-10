<?php

#: Before
use mysli\markdown;

# ------------------------------------------------------------------------------
#: Test Inline, Simple
$markdown = <<<MARKDOWN
This is a **bold**, __bold__, _italic_ and *italic*.
**bold**
__bold__
`code`
_italic_
*italic*
~sub~
^sup^
++inserted++
==marked==
~~strikethrough~~
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p>This is a <strong>bold</strong>, <strong>bold</strong>, <em>italic</em> and <em>italic</em>.
    <strong>bold</strong>
    <strong>bold</strong>
    <code>code</code>
    <em>italic</em>
    <em>italic</em>
    <sub>sub</sub>
    <sup>sup</sup>
    <ins>inserted</ins>
    <mark>marked</mark>
    <s>strikethrough</s></p>');

# ------------------------------------------------------------------------------
#: Test Inline, Complex
$markdown = <<<MARKDOWN
**b**
An _em_.
_i_
`c`
***bold***
****bold****
`` `code` ``
_ _italic_ _
` *code* `
**bold\***
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p><strong>b</strong>
    An <em>em</em>.
    <em>i</em>
    <code>c</code>
    <strong><em>bold</em></strong>
    <strong><em>*bold*</em></strong>
    <code>`code`</code>
    _ <em>italic</em> _
    <code>*code*</code>
    <strong>bold*</strong></p>');

# ------------------------------------------------------------------------------
#: Test Inline Code
$markdown = <<<MARKDOWN
`Inline 'code', "code", <code>, **code**, ^code^, ~code~, code!!!!!, (tm), (c)...`
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p><code>Inline \'code\', "code", <code>, **code**, ^code^, ~code~, code!!!!!, (tm), (c)...</code></p>');

# ------------------------------------------------------------------------------
#: Test Multiple Per Line
$markdown = <<<MARKDOWN
An _em_, **b**, _i_, `c`, ***bold***, ****bold****, `` `code` ``, _ _italic_ _, ` *code* `, **bold\***
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p>An <em>em</em>, <strong>b</strong>, <em>i</em>, <code>c</code>, '.
'<strong><em>bold</em></strong>, <strong><em>*bold*</em></strong>, '.
'<code>`code`</code>, _ <em>italic</em> _, <code>*code*</code>, '.
'<strong>bold*</strong></p>');

# ------------------------------------------------------------------------------
#: Test Inline, Multiple Lines
$markdown = <<<MARKDOWN
This _em goes through
multiple
various lines_.
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p>This <em>em goes through
    multiple
    various lines</em>.</p>');
