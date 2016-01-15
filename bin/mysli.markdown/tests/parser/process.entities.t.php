<?php

#: Before
use mysli\markdown;

#: Test Entities
$markdown = <<<MARKDOWN
&copy;

ME&YOU

4 < 5

4<5

5 > 4

5>4

4 &lt; 5

5 &gt; 4

http://my-link/?q=1&b=2&c=3

less<more and more>less

<div>Hello</div>

less < more and more > less

<img src="" />

"Please, enter your name!"
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>&copy;</p>
<p>ME&amp;YOU</p>
<p>4 &lt; 5</p>
<p>4&lt;5</p>
<p>5 &gt; 4</p>
<p>5&gt;4</p>
<p>4 &lt; 5</p>
<p>5 &gt; 4</p>
<p>http://my-link/?q=1&amp;b=2&amp;c=3</p>
less<more and more>less
<div>Hello</div>
<p>less &lt; more and more &gt; less</p>
<p><img src="" /></p>
<p>&ldquo;Please, enter your name!&rdquo;</p>');
