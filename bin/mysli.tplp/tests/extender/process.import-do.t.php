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
    ::import sidebar from _modules do
        ::set before
            <p>Before...</p>
        ::/set
        ::set after
            <p>After...</p>
        ::/set
    ::/import
</body>
</html>
BASE
,
    fs::tmppath('dev.test/_modules') => <<<'_MODULES'
::module sidebar
<div class="sidebar">
    ::print before
    <p>Hello world!</p>
    ::print after
</div>
::/module
_MODULES
];

#: Test Import Do
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
            <p>Before...</p>
    <p>Hello world!</p>
            <p>After...</p>
</div>
</body>
</html>
EXPECT
);
