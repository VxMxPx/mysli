<?php

#: Before
use mysli\markdown;

#: Test Links Basic
$markdown = <<<MARKDOWN
http://domain.tld

http://domain.tld/index.php http://domain.tld/index.php

me@domain.tld
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p><a href="http://domain.tld">http://domain.tld</a></p>
<p><a href="http://domain.tld/index.php">http://domain.tld/index.php</a> <a href="http://domain.tld/index.php">http://domain.tld/index.php</a></p>
<p><a href="mailto:me@domain.tld">me@domain.tld</a></p>');
