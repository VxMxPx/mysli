<?php

#: Before
use mysli\markdown;

#: Test Simple Code
# ~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
Here is an example of AppleScript:

    tell application "Foo"
        beep
    end tell
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>Here is an example of AppleScript:</p>
<pre><code>tell application "Foo"
    beep
end tell</code></pre>');

#: Test No Inlne Tags in Code
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Expect String <pre><code>This is **bold** and _italic_ text...</code></pre>
$markdown = <<<MARKDOWN
    This is **bold** and _italic_ text...
MARKDOWN;

return markdown::process($markdown);

#: Test Code in HTML
# ~~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
    <h1>Hello</h1>
    <h2>World</h2>
    <p>Paragraph</p>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<pre><code>&lt;h1&gt;Hello&lt;/h1&gt;
&lt;h2&gt;World&lt;/h2&gt;
&lt;p&gt;Paragraph&lt;/p&gt;</code></pre>');

#: Test No Code in HTML
# ~~~~~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
<div>
    <h1>Hello</h1>
    <h2>World</h2>
    <p>Paragraph</p>
</div>
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<div>
<h1>Hello</h1>
<h2>World</h2>
<p>Paragraph</p>
</div>');

#: Test Backtick Code
# ~~~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
```
Code!
```

````
More code
````
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<pre><code>Code!</code></pre>
<pre><code>More code</code></pre>');

#: Test Backtick Code, Class
# ~~~~~~~~~~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
``` php
if foo
    bar
```
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<pre><code class="language-php">if foo
    bar</code></pre>');

#: Test Previous Line Must be Empty
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$markdown = <<<MARKDOWN
Here is an example of AppleScript:
    tell application "Foo"
        beep
    end tell
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>Here is an example of AppleScript:
    tell application &ldquo;Foo&rdquo;
    beep
    end tell</p>');
