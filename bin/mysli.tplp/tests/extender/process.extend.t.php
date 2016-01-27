<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
$files = [
    fs::tmppath('dev.test/base') => <<<'BASE'
::extend _layout set content
<div>
    Some content here...
</div>
BASE
,
    fs::tmppath('dev.test/_layout') => <<<'_LAYOUT'
<!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    ::print content
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
?><!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>
EXPECT
);
