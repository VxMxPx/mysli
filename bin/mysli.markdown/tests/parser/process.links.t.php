<?php

#: Before
use mysli\markdown;
use mysli\markdown\parser;

#: Test Links Basic
#                                                                       --------
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
#                                                                       --------
$markdown = <<<MARKDOWN
[Domain](http://domain.tld), [Domain 2](http://domain2.tld)

[URL Encoded](https://domain.tld/entry=1?param=["one","two","three"]&param2=foo_bar_baz)

**[BOLD](http://domain.tld "Domain")**
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><a href="http://domain.tld">Domain</a>, <a href="http://domain2.tld">Domain 2</a></p>
<p><a href="https://domain.tld/entry=1?param=[%22one%22,%22two%22,%22three%22]&amp;param2=foo_bar_baz">URL Encoded</a></p>
<p><strong><a href="http://domain.tld" title="Domain">BOLD</a></strong></p>');

#: Test Replace Local Url
#                                                                       --------
$markdown = <<<MARKDOWN
![](/media/icon.jpg)
![](http://domain.tld/)
![](https://domain.tld)
![](mailto:me@domain.tld)
![](#scroll-top)
MARKDOWN;

$parser = new parser($markdown);
$link = $parser->get_processor('mysli.markdown.module.link');
$link->set_local_url('#^/(.*)$#', '/pages/unique-id/');

return assert::equals(markdown::process($parser),
'<p><img src="/pages/unique-id/media/icon.jpg" alt="" />
    <img src="http://domain.tld/" alt="" />
    <img src="https://domain.tld" alt="" />
    <img src="mailto:me@domain.tld" alt="" />
    <img src="#scroll-top" alt="" /></p>');

#: Test Multi Line Link
#                                                                       --------
$markdown = <<<MARKDOWN
Thank your for using our product. This is an early alpha version,
there might be bugs. If you find any, please report it [here](#bugs).
MARKDOWN;
return assert::equals(markdown::process($markdown),
'<p>Thank your for using our product. This is an early alpha version,
    there might be bugs. If you find any, please report it <a href="#bugs">here</a>.</p>');


#: Test Replace Local Url, Only JPG
#                                                                       --------
$markdown = <<<MARKDOWN
[A](/media/icon.jpg)
[B](/media/icon.png)
[C](/media/icon.html)
MARKDOWN;

$parser = new parser($markdown);
$link = $parser->get_processor('mysli.markdown.module.link');
$link->set_local_url('#^/(.*)\.jpg$#', '/pages/unique-id/');

return assert::equals(markdown::process($parser),
'<p><a href="/pages/unique-id/media/icon.jpg">A</a>
    <a href="/media/icon.png">B</a>
    <a href="/media/icon.html">C</a></p>');

#: Test Image in Link
#                                                                       --------
$markdown = <<<MARKDOWN
[![](http://domain.tld/thumb-image.jpg)](http://domain.tld/image.jpg)
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><a href="http://domain.tld/image.jpg"><img src="http://domain.tld/thumb-image.jpg" alt="" /></a></p>');

#: Test Video Link
#                                                                       --------
$markdown = <<<MARKDOWN
~[](http://domain.tld/video.webm)
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><video src="http://domain.tld/video.webm" controls>
    Sorry, your browser doesn\'t support embedded videos,
    but don\'t worry, you can <a href="http://domain.tld/video.webm">download it</a>
    and watch it with your favorite video player!
</video></p>');
