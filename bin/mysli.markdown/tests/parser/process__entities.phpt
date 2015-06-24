--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
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

less < more and more > less

<p></p>

<img src="" />

"Please, enter your name!"
EOF
);
?>
--EXPECT--
<p>&copy;</p>
<p>ME&amp;YOU</p>
<p>4 &lt; 5</p>
<p>4&lt;5</p>
<p>5 &gt; 4</p>
<p>5&gt;4</p>
<p>4 &lt; 5</p>
<p>5 &gt; 4</p>
<p>http://my-link/?q=1&amp;b=2&amp;c=3</p>
<p>less<more and more>less</p>
<p>less &lt; more and more &gt; less</p>
<p></p>
<p><img src="" /></p>
<p>"Please, enter your name!"</p>
