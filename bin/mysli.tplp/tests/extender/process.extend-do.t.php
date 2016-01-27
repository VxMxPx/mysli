<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
$files = [
    fs::tmppath('dev.test/base') => <<<'BASE'
::extend _layout set content do
    ::set styles
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
    ::/set
::/extend
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
    ::print styles
</head>
<body>
    ::print content
</body>
</html>
_LAYOUT
];

#: Test Extend Do
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
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>
EXPECT
);
