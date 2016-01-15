<?php

#: Before
use mysli\markdown;

#: Test Links Basic
$markdown = <<<MARKDOWN
[Domain](http://domain.tld)

[Domain](http://domain.tld "Hello")

![Image](http://domain.tld)

![](http://domain.tld)

![Image](http://domain.tld "Hello")
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><a href="http://domain.tld">Domain</a></p>
<p><a href="http://domain.tld" title="Hello">Domain</a></p>
<p><img src="http://domain.tld" alt="Image" /></p>
<p><img src="http://domain.tld" alt="" /></p>
<p><img src="http://domain.tld" alt="Image" title="Hello" /></p>');

#: Test Links Advanced
$markdown = <<<MARKDOWN
[Domain](http://domain.tld), [Domain 2](http://domain2.tld)

[URL Encoded](https://domain.tld/entry=1?param=["one","two","three"]&param2=foo_bar_baz)

**[BOLD](http://domain.tld "Domain")**
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><a href="http://domain.tld">Domain</a>, <a href="http://domain2.tld">Domain 2</a></p>
<p><a href="https://domain.tld/entry=1?param=[%22one%22,%22two%22,%22three%22]&amp;param2=foo_bar_baz">URL Encoded</a></p>
<p><strong><a href="http://domain.tld" title="Domain">BOLD</a></strong></p>');
