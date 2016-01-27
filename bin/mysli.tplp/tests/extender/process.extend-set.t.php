<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
$files = [
    fs::tmppath('dev.test/base') => <<<'BASE'
    ::extend _layout set contents
    <p>Contents</p>
BASE
,
    fs::tmppath('dev.test/_layout') => <<<'_LAYOUT'
<html>
<body>
    ::print contents
</body>
</html>
_LAYOUT
];

#: Test Extend Set
#: Use Files
$extender = new extender(fs::tmppath('dev.test'));
foreach ($files as $id => $template) $extender->set_cache($id, $template);
$template = $extender->process('base');

return assert::equals(
    $template,
    <<<'EXPECT'
<?php
namespace tplp\template\base;
?><html>
<body>
    <p>Contents</p>
</body>
</html>
EXPECT
);
