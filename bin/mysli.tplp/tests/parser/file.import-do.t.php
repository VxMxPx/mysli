<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$base = <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
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
TEST;
file::write(fs::tmppath('dev.test/~test.tpl.html'), $base);

$_modules = <<<'_MODULES'
::module sidebar
<div class="sidebar">
    ::print before
    <p>Hello world!</p>
    ::print after
</div>
::/module
_MODULES;
file::write(fs::tmppath('dev.test/_modules.tpl.html'), $_modules);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));
file::remove(fs::tmppath('dev.test/_modules.tpl.html'));


#: Test Import Do
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Import
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
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
