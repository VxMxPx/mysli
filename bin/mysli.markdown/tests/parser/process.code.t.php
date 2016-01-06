<?php

#: Before
use mysli\markdown;

#: Test Blockquote Containing
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
