<?php

#: Before
use mysli\markdown;

#: Test Simple HTML tags
$markdown = <<<MARKDOWN
<small>
    Also, _inline_ tags will be matched **here**.
</small>

<div>This is a div.</div>

<div>
There are some _inline_ tags here too, which will not be skipped.
</div>

<a href="#top">Top</a>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><small>
    Also, <em>inline</em> tags will be matched <strong>here</strong>.
    </small></p>
<div>This is a div.</div>
<div>
There are some <em>inline</em> tags here too, which will not be skipped.
</div>
<p><a href="#top">Top</a></p>');

#: Test Process Within HTML Tags
$markdown = <<<MARKDOWN
<small>
    - one
    - two
    - three
    - four
</small>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><small>
    - one
    - two
    - three
    - four
    </small></p>');


#: Test Process Unclosed, Follow
$markdown = <<<MARKDOWN
<div>

<img src="foo"/>

<hr/>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><div></p>
<p><img src="foo"/></p>
<p><hr/></p>');

#: Test Closed, Follow
$markdown = <<<MARKDOWN
<div>
    <img src="foo"/>
    <hr/>
</div>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<div>
<img src="foo"/>
<hr/>
</div>');

#: Test Multiple Tags
$markdown = <<<MARKDOWN
<ul class="columns-3">
    <li>One:<br><a href="#one">A</a></li>
    <li>Two:<br><a href="#two">B</a></li>
    <li>Three:<br><a href="#three">C</a></li>
</ul>
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<ul class="columns-3">
<li>One:<br><a href="#one">A</a></li>
<li>Two:<br><a href="#two">B</a></li>
<li>Three:<br><a href="#three">C</a></li>
</ul>');
