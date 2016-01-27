<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
    $files = [
        fs::tmppath('dev.test/base') => <<<'BASE'
<!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    ::import _sidebar
</body>
</html>
BASE
,
    fs::tmppath('dev.test/_sidebar') => <<<'_SIDEBAR'
<div class="sidebar">
    <p>Hello world!</p>
</div>
_SIDEBAR
,
    fs::tmppath('dev.test/error') => <<<'ERROR'
<!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    ::import _non_existant_file
</body>
</html>
ERROR
];

#: Test Import
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
<div class="sidebar">
    <p>Hello world!</p>
</div>
</body>
</html>
EXPECT
);

#: Test Import Exception
#: Use Files
#: Expect Exception mysli\tplp\exception\extender 10
$extender = new extender(fs::tmppath('dev.test'));
foreach ($files as $id => $template) $extender->set_cache($id, $template);
$extender->process('error');
