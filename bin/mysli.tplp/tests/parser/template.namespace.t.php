<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$base = <<<'TEST'
<div>
    Some content here...
</div>
TEST;

#: Test Extend
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($base, 'foo\\bar\\baz'),
    <<<'EXPECT'
<?php
// NAMESPACE:
namespace foo\bar\baz;
?><div>
    Some content here...
</div>
EXPECT
);
